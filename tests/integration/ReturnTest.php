<?php

/*
 * This file is part of fof/impersonate.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Impersonate\Tests\integration;

use Flarum\Audit\AuditLog;
use Flarum\Audit\AuditLogger;
use Flarum\Extend\Csrf;
use Flarum\Group\Group;
use Flarum\Http\AccessToken;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;

class ReturnTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        // Lifecycle events fired outside the test transaction shouldn't create stray entries.
        AuditLogger::$testMode = true;

        $this->extension('flarum-audit', 'fof-impersonate');

        $this->extend(
            (new Csrf())->exemptRoute('login'),
            (new Csrf())->exemptRoute('fof.impersonate.api.login'),
            (new Csrf())->exemptRoute('fof.impersonate.api.return')
        );

        $this->prepareDatabase([
            'audit_log' => [],
            User::class => [
                $this->normalUser(),
                [
                    'id'       => 3,
                    'username' => 'user3',
                    'email'    => 'user3@example.com',
                ],
                [
                    'id'       => 4,
                    'username' => 'user4',
                    'email'    => 'user4@example.com',
                ],
            ],
            'group_permission' => [
                // Lets user3 impersonate in the chained impersonation test.
                ['permission' => 'fof-impersonate.login', 'group_id' => Group::MEMBER_ID],
            ],
        ]);
    }

    protected function loginAsAdmin(bool $remember = false): ResponseInterface
    {
        return $this->send($this->request('POST', '/login', [
            'json' => [
                'identification' => 'admin',
                'password'       => 'password',
                'remember'       => $remember,
            ],
        ]));
    }

    protected function rememberTokenCount(int $userId): int
    {
        return AccessToken::query()->where('user_id', $userId)->where('type', 'session_remember')->count();
    }

    protected function impersonate(ResponseInterface $cookiesFrom, int $userId): ResponseInterface
    {
        return $this->send($this->request('POST', '/api/impersonate', [
            'cookiesFrom' => $cookiesFrom,
            'json'        => [
                'data' => [
                    'attributes' => [
                        'userId' => $userId,
                        'reason' => 'testing',
                    ],
                ],
            ],
        ]));
    }

    protected function returnToOriginalUser(?ResponseInterface $cookiesFrom = null): ResponseInterface
    {
        $options = $cookiesFrom ? ['cookiesFrom' => $cookiesFrom] : [];

        return $this->send($this->request('POST', '/api/impersonate/return', $options));
    }

    #[Test]
    public function return_restores_original_user()
    {
        $adminSession = $this->loginAsAdmin();

        $impersonated = $this->impersonate($adminSession, 3);
        $this->assertEquals(200, $impersonated->getStatusCode());

        $returned = $this->returnToOriginalUser($impersonated);
        $this->assertEquals(200, $returned->getStatusCode());

        // The impersonated user's session token must be gone.
        $this->assertEquals(0, AccessToken::query()->where('user_id', 3)->count());

        $log = AuditLog::query()->where('action', 'user.impersonation_ended')->first();
        $this->assertNotNull($log);
        $this->assertEquals(3, $log->actor_id);
        $this->assertEquals(['user_id' => 1], $log->payload);

        // A session-only login stays session-only: no remember token is minted.
        $this->assertEquals(0, $this->rememberTokenCount(1));

        // The session is the admin's again: impersonating requires the permission,
        // so a successful second impersonation proves the actor was restored.
        $this->assertEquals(200, $this->impersonate($returned, 3)->getStatusCode());
    }

    #[Test]
    public function return_restores_remember_session()
    {
        $adminSession = $this->loginAsAdmin(remember: true);
        $this->assertEquals(1, $this->rememberTokenCount(1));

        $impersonated = $this->impersonate($adminSession, 3);
        $this->assertEquals(200, $impersonated->getStatusCode());

        // Impersonating replaces the remember login: the token is deleted, not orphaned.
        $this->assertEquals(0, $this->rememberTokenCount(1));

        $returned = $this->returnToOriginalUser($impersonated);
        $this->assertEquals(200, $returned->getStatusCode());

        // Returning mints a fresh remember token and sets its cookie.
        $this->assertEquals(1, $this->rememberTokenCount(1));

        $rememberToken = AccessToken::query()->where('user_id', 1)->where('type', 'session_remember')->first();
        $this->assertStringContainsString($rememberToken->token, implode(';', $returned->getHeader('Set-Cookie')));
    }

    #[Test]
    public function return_without_impersonating_is_denied()
    {
        $adminSession = $this->loginAsAdmin();

        $response = $this->returnToOriginalUser($adminSession);

        $this->assertEquals(403, $response->getStatusCode());
    }

    #[Test]
    public function return_as_guest_is_denied()
    {
        $response = $this->returnToOriginalUser();

        $this->assertEquals(403, $response->getStatusCode());
    }

    #[Test]
    public function chained_impersonation_returns_to_first_user()
    {
        $adminSession = $this->loginAsAdmin();

        $asUser3 = $this->impersonate($adminSession, 3);
        $this->assertEquals(200, $asUser3->getStatusCode());

        $asUser4 = $this->impersonate($asUser3, 4);
        $this->assertEquals(200, $asUser4->getStatusCode());

        $returned = $this->returnToOriginalUser($asUser4);
        $this->assertEquals(200, $returned->getStatusCode());

        // Returning skips the middle of the chain and restores the admin.
        $log = AuditLog::query()->where('action', 'user.impersonation_ended')->first();
        $this->assertNotNull($log);
        $this->assertEquals(4, $log->actor_id);
        $this->assertEquals(['user_id' => 1], $log->payload);
    }

    #[Test]
    public function return_after_logging_into_another_account_is_denied()
    {
        $adminSession = $this->loginAsAdmin();

        $impersonated = $this->impersonate($adminSession, 3);
        $this->assertEquals(200, $impersonated->getStatusCode());

        // Log into an unrelated account within the impersonating session. Core's
        // login preserves session attributes, so the markers survive this and must
        // not hand this account the admin's return path.
        $loggedIn = $this->send($this->request('POST', '/login', [
            'cookiesFrom' => $impersonated,
            'json'        => [
                'identification' => 'normal',
                'password'       => 'too-obscure',
            ],
        ]));
        $this->assertEquals(200, $loggedIn->getStatusCode());

        $this->assertEquals(403, $this->returnToOriginalUser($loggedIn)->getStatusCode());
    }

    #[Test]
    public function return_logs_out_when_original_user_is_gone()
    {
        $adminSession = $this->loginAsAdmin();

        $impersonated = $this->impersonate($adminSession, 3);
        $this->assertEquals(200, $impersonated->getStatusCode());

        User::query()->where('id', 1)->delete();

        $returned = $this->returnToOriginalUser($impersonated);
        $this->assertEquals(200, $returned->getStatusCode());

        // The session was ended entirely, so a second return finds a guest.
        $this->assertEquals(403, $this->returnToOriginalUser($returned)->getStatusCode());
    }
}
