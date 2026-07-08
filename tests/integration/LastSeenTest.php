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

use Flarum\Extend\Csrf;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;

class LastSeenTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-impersonate');

        $this->extend(
            (new Csrf())->exemptRoute('login'),
            (new Csrf())->exemptRoute('fof.impersonate.api.login'),
            (new Csrf())->exemptRoute('fof.impersonate.api.return')
        );

        $this->prepareDatabase([
            User::class => [
                [
                    'id'       => 3,
                    'username' => 'user3',
                    'email'    => 'user3@example.com',
                ],
            ],
        ]);
    }

    protected function loginAsAdmin(): ResponseInterface
    {
        return $this->send($this->request('POST', '/login', [
            'json' => [
                'identification' => 'admin',
                'password'       => 'password',
            ],
        ]));
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

    protected function lastSeenAt(int $userId): ?string
    {
        return User::query()->find($userId)->getRawOriginal('last_seen_at');
    }

    #[Test]
    public function browsing_while_impersonated_does_not_update_last_seen()
    {
        $adminSession = $this->loginAsAdmin();

        $impersonated = $this->impersonate($adminSession, 3);
        $this->assertEquals(200, $impersonated->getStatusCode());

        // Browse the forum as the impersonated user.
        $response = $this->send($this->request('GET', '/api', [
            'cookiesFrom' => $impersonated,
        ]));
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertNull($this->lastSeenAt(3));
    }

    #[Test]
    public function browsing_normally_updates_last_seen()
    {
        // Sanity check that the guard doesn't suppress regular activity: the
        // admin's own login session updates their last_seen_at.
        $adminSession = $this->loginAsAdmin();

        $response = $this->send($this->request('GET', '/api', [
            'cookiesFrom' => $adminSession,
        ]));
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertNotNull($this->lastSeenAt(1));
    }

    #[Test]
    public function browsing_after_returning_updates_last_seen_again()
    {
        $adminSession = $this->loginAsAdmin();

        $impersonated = $this->impersonate($adminSession, 3);
        $this->assertEquals(200, $impersonated->getStatusCode());

        $returned = $this->send($this->request('POST', '/api/impersonate/return', [
            'cookiesFrom' => $impersonated,
        ]));
        $this->assertEquals(200, $returned->getStatusCode());

        $response = $this->send($this->request('GET', '/api', [
            'cookiesFrom' => $returned,
        ]));
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertNotNull($this->lastSeenAt(1));
        $this->assertNull($this->lastSeenAt(3));
    }
}
