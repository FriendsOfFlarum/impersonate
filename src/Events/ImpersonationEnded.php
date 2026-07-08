<?php

/*
 * This file is part of fof/impersonate.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Impersonate\Events;

use Flarum\User\User;

class ImpersonationEnded
{
    /**
     * @param User $user         The account that was being impersonated.
     * @param User $originalUser The account that was returned to.
     */
    public function __construct(public User $user, public User $originalUser)
    {
    }
}
