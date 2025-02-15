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
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;

class UserResourceFields
{
    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {
    }

    public function __invoke(): array
    {
        return [
            Schema\Boolean::make('canFoFImpersonate')
                ->visible(fn (User $user, Context $context) => $context->getActor()->can('fofCanImpersonate', $user))
                ->get(function (User $user, Context $context) {
                    $actor = $context->getActor();

                    return $actor->can('fofCanImpersonate', $user);
                }),
            Schema\Boolean::make('impersonateReasonRequired')
            ->visible(fn (User $user, Context $context) => $context->getActor()->can('fofCanImpersonate', $user))
                ->get(function (User $user, Context $context) {
                    return $this->settings->get('fof-impersonate.require_reason');
                }),
        ];
    }
}
