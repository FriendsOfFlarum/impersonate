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

use Flarum\Api\Resource;
use Flarum\Extend;
use Flarum\User\User;
use FoF\Impersonate\Events\Impersonated;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    new Extend\Locales(__DIR__.'/resources/locale'),

    (new Extend\Routes('api'))
        ->post('/impersonate', 'fof.impersonate.api.login', Controllers\LoginController::class),

    (new Extend\ApiResource(Resource\UserResource::class))
        ->fields(Api\UserResourceFields::class),

    (new Extend\Policy())
        ->modelPolicy(User::class, Access\UserPolicy::class),

    (new Extend\Conditional())
        ->whenExtensionEnabled('flarum-audit', fn () => [
            (new \Flarum\Audit\Extend\Audit())
                ->group('fof-impersonate')
                ->listen(Impersonated::class, 'user.impersonated', fn ($e) => [
                    'user_id' => $e->user->id,
                    'reason'  => $e->switchReason ?: null,
                ]),
        ]),
];
