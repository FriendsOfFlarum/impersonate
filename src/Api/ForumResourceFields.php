<?php

/*
 * This file is part of fof/impersonate.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Impersonate\Api;

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\User\User;
use FoF\Impersonate\Impersonation;
use Illuminate\Contracts\Session\Session;

class ForumResourceFields
{
    public function __invoke(): array
    {
        return [
            Schema\Str::make('fofImpersonateOriginalUsername')
                ->visible(fn ($model, Context $context) => $this->originalUser($context) !== null)
                ->get(fn ($model, Context $context) => $this->originalUser($context)?->username),
        ];
    }

    protected function originalUser(Context $context): ?User
    {
        /**
         * @var Session|null $session
         */
        $session = $context->request->getAttribute('session');

        $originalUserId = $session?->get(Impersonation::ORIGINAL_USER_SESSION_KEY);

        $actor = $context->getActor();

        if (!$originalUserId || $actor->isGuest()) {
            return null;
        }

        // Mirrors the return endpoint: after a credentialed login into a different
        // account the markers are stale, so don't advertise a return path that
        // would be denied.
        if ($actor->id !== (int) $session->get(Impersonation::IMPERSONATED_USER_SESSION_KEY)) {
            return null;
        }

        return User::query()->find($originalUserId);
    }
}
