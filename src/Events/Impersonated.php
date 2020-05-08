<?php

/*
 * This file is part of fof/impersonate.
 *
 * Copyright (c) 2020 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Impersonate\Events;

use Flarum\User\User;

class Impersonated
{
    /**
     * @var User
     */
    public $actor;

    /**
     * @var User
     */
    public $user;

    /**
     * Impersonated constructor.
     *
     * @param Discussion $discussion
     * @param User       $actor
     */
    public function __construct(User $actor, User $user)
    {
        $this->actor = $actor;
        $this->user = $user;
    }
}
