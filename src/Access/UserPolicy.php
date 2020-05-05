<?php

/*
 * This file is part of fof/impersonate.
 *
 * Copyright (c) 2020 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Impersonate\Access;

use Flarum\User\AbstractPolicy;
use Flarum\User\User;

class UserPolicy extends AbstractPolicy
{
    protected $model = User::class;

    public function fofCanImpersonate(User $actor, User $user)
    {
        return $actor->can('fof-impersonate.login') &&
            $actor->id !== $user->id &&
            (!$user->isAdmin() || $actor->isAdmin());
    }
}
