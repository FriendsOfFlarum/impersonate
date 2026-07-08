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
use Flarum\Group\Group;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;

class PermissionsTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    // BCrypt hash for "too-obscure", same as RetrievesAuthorizedUsers::normalUser().
    protected const PASSWORD_HASH = '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim';

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
                $this->normalUser(),
                [
                    'id'                 => 4,
                    'username'           => 'moduser',
                    'email'              => 'moduser@machine.local',
                    'password'           => self::PASSWORD_HASH,
                    'is_email_confirmed' => 1,
                ],
                [
                    'id'                 => 5,
                    'username'           => 'moduser2',
                    'email'              => 'moduser2@machine.local',
                    'is_email_confirmed' => 1,
                ],
                [
                    'id'                 => 6,
                    'username'           => 'admin2',
                    'email'              => 'admin2@machine.local',
                    'is_email_confirmed' => 1,
                ],
            ],
            'group_user' => [
                ['user_id' => 4, 'group_id' => Group::MODERATOR_ID],
                ['user_id' => 5, 'group_id' => Group::MODERATOR_ID],
                ['user_id' => 6, 'group_id' => Group::ADMINISTRATOR_ID],
            ],
            'group_permission' => [
                ['permission' => 'fof-impersonate.login', 'group_id' => Group::MODERATOR_ID],
            ],
        ]);
    }

    protected function loginAs(string $identification, string $password): ResponseInterface
    {
        return $this->send($this->request('POST', '/login', [
            'json' => compact('identification', 'password'),
        ]));
    }

    protected function impersonate(?ResponseInterface $cookiesFrom, int $userId): ResponseInterface
    {
        $options = [
            'json' => [
                'data' => [
                    'attributes' => [
                        'userId' => $userId,
                        'reason' => 'testing',
                    ],
                ],
            ],
        ];

        if ($cookiesFrom) {
            $options['cookiesFrom'] = $cookiesFrom;
        }

        return $this->send($this->request('POST', '/api/impersonate', $options));
    }

    protected function returnToOriginalUser(ResponseInterface $cookiesFrom): ResponseInterface
    {
        return $this->send($this->request('POST', '/api/impersonate/return', [
            'cookiesFrom' => $cookiesFrom,
        ]));
    }

    /* -------------------------------------------------------------------------
     * Moderators (permission granted to the group, no admin short-circuit)
     * ---------------------------------------------------------------------- */

    #[Test]
    public function moderator_can_impersonate_normal_user()
    {
        $modSession = $this->loginAs('moduser', 'too-obscure');

        $impersonated = $this->impersonate($modSession, 2);
        $this->assertEquals(200, $impersonated->getStatusCode());

        // The session is now the unprivileged user's: impersonating again fails,
        // proving the actor really switched.
        $this->assertEquals(403, $this->impersonate($impersonated, 5)->getStatusCode());
    }

    #[Test]
    public function moderator_can_impersonate_another_moderator()
    {
        $modSession = $this->loginAs('moduser', 'too-obscure');

        $this->assertEquals(200, $this->impersonate($modSession, 5)->getStatusCode());
    }

    #[Test]
    public function moderator_cannot_impersonate_admin()
    {
        $modSession = $this->loginAs('moduser', 'too-obscure');

        $this->assertEquals(403, $this->impersonate($modSession, 1)->getStatusCode());
        $this->assertEquals(403, $this->impersonate($modSession, 6)->getStatusCode());
    }

    #[Test]
    public function moderator_cannot_impersonate_self()
    {
        $modSession = $this->loginAs('moduser', 'too-obscure');

        $this->assertEquals(403, $this->impersonate($modSession, 4)->getStatusCode());
    }

    #[Test]
    public function moderator_can_return_to_own_account()
    {
        $modSession = $this->loginAs('moduser', 'too-obscure');

        $impersonated = $this->impersonate($modSession, 2);
        $this->assertEquals(200, $impersonated->getStatusCode());

        $returned = $this->returnToOriginalUser($impersonated);
        $this->assertEquals(200, $returned->getStatusCode());

        // Impersonating again succeeds, proving the moderator was restored.
        $this->assertEquals(200, $this->impersonate($returned, 2)->getStatusCode());
    }

    /* -------------------------------------------------------------------------
     * Admins
     * ---------------------------------------------------------------------- */

    #[Test]
    public function admin_can_impersonate_moderator()
    {
        $adminSession = $this->loginAs('admin', 'password');

        $this->assertEquals(200, $this->impersonate($adminSession, 4)->getStatusCode());
    }

    #[Test]
    public function admin_can_impersonate_another_admin()
    {
        $adminSession = $this->loginAs('admin', 'password');

        $this->assertEquals(200, $this->impersonate($adminSession, 6)->getStatusCode());
    }

    #[Test]
    public function admin_cannot_impersonate_self()
    {
        $adminSession = $this->loginAs('admin', 'password');

        // The policy's explicit deny applies even to admins.
        $this->assertEquals(403, $this->impersonate($adminSession, 1)->getStatusCode());
    }

    /* -------------------------------------------------------------------------
     * Users without the permission, guests, and escalation attempts
     * ---------------------------------------------------------------------- */

    #[Test]
    public function member_without_permission_cannot_impersonate()
    {
        $memberSession = $this->loginAs('normal', 'too-obscure');

        $this->assertEquals(403, $this->impersonate($memberSession, 5)->getStatusCode());
    }

    #[Test]
    public function guest_cannot_impersonate()
    {
        $this->assertEquals(403, $this->impersonate(null, 2)->getStatusCode());
    }

    #[Test]
    public function impersonated_user_cannot_escalate_by_impersonating_others()
    {
        $modSession = $this->loginAs('moduser', 'too-obscure');

        // Switch to a user without the permission...
        $impersonated = $this->impersonate($modSession, 2);
        $this->assertEquals(200, $impersonated->getStatusCode());

        // ...who must not inherit the moderator's ability to impersonate.
        $this->assertEquals(403, $this->impersonate($impersonated, 5)->getStatusCode());
        $this->assertEquals(403, $this->impersonate($impersonated, 1)->getStatusCode());
    }

    /* -------------------------------------------------------------------------
     * API attribute visibility
     * ---------------------------------------------------------------------- */

    #[Test]
    public function user_resource_exposes_impersonation_ability_to_moderator()
    {
        $response = $this->send($this->request('GET', '/api/users/2', [
            'authenticatedAs' => 4,
        ]));
        $this->assertEquals(200, $response->getStatusCode());

        $attributes = json_decode($response->getBody()->getContents(), true)['data']['attributes'];
        $this->assertTrue($attributes['canFoFImpersonate']);
    }

    #[Test]
    public function user_resource_hides_impersonation_ability_for_admin_target()
    {
        $response = $this->send($this->request('GET', '/api/users/1', [
            'authenticatedAs' => 4,
        ]));
        $this->assertEquals(200, $response->getStatusCode());

        $attributes = json_decode($response->getBody()->getContents(), true)['data']['attributes'];
        $this->assertArrayNotHasKey('canFoFImpersonate', $attributes);
    }

    #[Test]
    public function user_resource_hides_impersonation_ability_from_member()
    {
        $response = $this->send($this->request('GET', '/api/users/5', [
            'authenticatedAs' => 2,
        ]));
        $this->assertEquals(200, $response->getStatusCode());

        $attributes = json_decode($response->getBody()->getContents(), true)['data']['attributes'];
        $this->assertArrayNotHasKey('canFoFImpersonate', $attributes);
    }
}
