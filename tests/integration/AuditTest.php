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
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;

class AuditTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        // Lifecycle events fired outside the test transaction shouldn't create stray entries.
        AuditLogger::$testMode = true;

        $this->extension('flarum-audit', 'fof-impersonate');

        $this->extend((new Csrf())->exemptRoute('login'));

        $this->prepareDatabase([
            'audit_log' => [],
            User::class => [
                [
                    'id'       => 3,
                    'username' => 'user3',
                    'email'    => 'user3@example.com',
                ],
            ],
        ]);
    }

    #[Test]
    public function impersonate()
    {
        $adminSession = $this->send($this->request('POST', '/login', [
            'json' => [
                'identification' => 'admin',
                'password'       => 'password',
            ],
        ]));

        // We can't use authenticateAs because Impersonate only works with sessions and not access tokens
        $response = $this->send($this->request('POST', '/api/impersonate', [
            'cookiesFrom' => $adminSession,
            'json'        => [
                'data' => [
                    'attributes' => [
                        'userId' => 3,
                        'reason' => 'because', // Currently not optional, it would result in undefined index if not included
                    ],
                ],
            ],
        ])->withAddedHeader('X-CSRF-Token', $adminSession->getHeaderLine('X-CSRF-Token')));

        $this->assertEquals(200, $response->getStatusCode());

        $log = AuditLog::query()->where('action', 'user.impersonated')->first();
        $this->assertNotNull($log);
        $this->assertEquals(1, $log->actor_id);
        $this->assertEquals([
            'user_id' => 3,
            'reason'  => 'because',
        ], $log->payload);
    }
}
