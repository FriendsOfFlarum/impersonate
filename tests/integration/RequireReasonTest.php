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
use FoF\ModeratorNotes\Model\ModeratorNote;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;

class RequireReasonTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        // Lifecycle events fired outside the test transaction shouldn't create stray entries.
        AuditLogger::$testMode = true;

        $this->extend(
            (new Csrf())->exemptRoute('login'),
            (new Csrf())->exemptRoute('fof.impersonate.api.login')
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

    protected function impersonate(?string $reason): ResponseInterface
    {
        $adminSession = $this->send($this->request('POST', '/login', [
            'json' => [
                'identification' => 'admin',
                'password'       => 'password',
            ],
        ]));

        $attributes = ['userId' => 3];

        if ($reason !== null) {
            $attributes['reason'] = $reason;
        }

        return $this->send($this->request('POST', '/api/impersonate', [
            'cookiesFrom' => $adminSession,
            'json'        => [
                'data' => ['attributes' => $attributes],
            ],
        ]));
    }

    /* -------------------------------------------------------------------------
     * Activation via flarum/audit
     * ---------------------------------------------------------------------- */

    #[Test]
    public function reason_is_required_when_audit_is_enabled()
    {
        $this->extension('flarum-audit', 'fof-impersonate');
        $this->setting('fof-impersonate.require_reason', '1');
        $this->prepareDatabase(['audit_log' => []]);

        $this->assertEquals(422, $this->impersonate(null)->getStatusCode());
        $this->assertEquals(422, $this->impersonate('')->getStatusCode());
    }

    #[Test]
    public function reason_is_accepted_and_logged_when_audit_is_enabled()
    {
        $this->extension('flarum-audit', 'fof-impersonate');
        $this->setting('fof-impersonate.require_reason', '1');
        $this->prepareDatabase(['audit_log' => []]);

        $this->assertEquals(200, $this->impersonate('support ticket #123')->getStatusCode());

        $log = AuditLog::query()->where('action', 'user.impersonated')->first();
        $this->assertNotNull($log);
        $this->assertEquals('support ticket #123', $log->payload['reason']);
    }

    #[Test]
    public function reason_is_optional_when_audit_is_enabled_but_setting_is_off()
    {
        $this->extension('flarum-audit', 'fof-impersonate');
        $this->prepareDatabase(['audit_log' => []]);

        $this->assertEquals(200, $this->impersonate(null)->getStatusCode());
    }

    /* -------------------------------------------------------------------------
     * Activation via fof/moderator-notes
     * ---------------------------------------------------------------------- */

    #[Test]
    public function reason_is_required_when_moderator_notes_is_enabled()
    {
        $this->extension('fof-moderator-notes', 'fof-impersonate');
        $this->setting('fof-impersonate.require_reason', '1');

        $this->assertEquals(422, $this->impersonate(null)->getStatusCode());
        $this->assertEquals(422, $this->impersonate('')->getStatusCode());
    }

    #[Test]
    public function reason_is_accepted_and_noted_when_moderator_notes_is_enabled()
    {
        $this->extension('fof-moderator-notes', 'fof-impersonate');
        $this->setting('fof-impersonate.require_reason', '1');

        $this->assertEquals(200, $this->impersonate('support ticket #123')->getStatusCode());

        // moderator-notes listens to our Impersonated event and leaves a note on
        // both the impersonated user and the actor. (The reason is embedded via
        // its translations, which don't render in the test environment — the
        // formatting is moderator-notes' contract, not ours.)
        $notes = ModeratorNote::query()->orderBy('user_id')->get();
        $this->assertCount(2, $notes);
        $this->assertEquals([1, 3], $notes->pluck('user_id')->all());
        $this->assertEquals([1, 1], $notes->pluck('added_by_user_id')->all());
    }

    /* -------------------------------------------------------------------------
     * No recording extension enabled: the requirement is inactive
     * ---------------------------------------------------------------------- */

    #[Test]
    public function reason_is_not_required_without_a_recording_extension()
    {
        $this->extension('fof-impersonate');
        $this->setting('fof-impersonate.require_reason', '1');

        $this->assertEquals(200, $this->impersonate(null)->getStatusCode());
    }
}
