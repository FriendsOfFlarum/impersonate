<?php

/*
 * This file is part of fof/impersonate.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Impersonate;

use Flarum\Foundation\AbstractServiceProvider;
use Flarum\User\User;

class ImpersonateServiceProvider extends AbstractServiceProvider
{
    public function boot(): void
    {
        // Impersonation must leave no activity trace on the target account: freeze
        // last_seen_at (which also drives the "online" indicator) whenever a save
        // during an impersonated request would bump it. Request-scoped, so the real
        // user's own devices still update it normally.
        User::saving(function (User $user) {
            if (Impersonation::$impersonatedUserId === $user->id && $user->isDirty('last_seen_at')) {
                $user->last_seen_at = $user->getOriginal('last_seen_at');
            }
        });
    }
}
