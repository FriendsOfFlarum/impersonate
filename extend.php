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
use FoF\Impersonate\Events\ImpersonationEnded;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    (new Extend\Frontend('common'))
        ->jsDirectory(__DIR__.'/js/dist/common'),

    new Extend\Locales(__DIR__.'/resources/locale'),

    (new Extend\ServiceProvider())
        ->register(ImpersonateServiceProvider::class),

    (new Extend\Middleware('forum'))
        ->insertBefore(\Flarum\Http\Middleware\AuthenticateWithSession::class, Middleware\DetectImpersonation::class),

    (new Extend\Middleware('admin'))
        ->insertBefore(\Flarum\Http\Middleware\AuthenticateWithSession::class, Middleware\DetectImpersonation::class),

    (new Extend\Middleware('api'))
        ->insertBefore(\Flarum\Http\Middleware\AuthenticateWithSession::class, Middleware\DetectImpersonation::class),

    (new Extend\Routes('api'))
        ->post('/impersonate', 'fof.impersonate.api.login', Controllers\LoginController::class)
        ->post('/impersonate/return', 'fof.impersonate.api.return', Controllers\ReturnController::class),

    (new Extend\ApiResource(Resource\UserResource::class))
        ->fields(Api\UserResourceFields::class),

    (new Extend\ApiResource(Resource\ForumResource::class))
        ->fields(Api\ForumResourceFields::class),

    (new Extend\Policy())
        ->modelPolicy(User::class, Access\UserPolicy::class),

    (new Extend\Conditional())
        ->whenExtensionEnabled('flarum-audit', fn () => [
            (new \Flarum\Audit\Extend\Audit())
                ->group('fof-impersonate')
                ->listen(Impersonated::class, 'user.impersonated', fn ($e) => [
                    'user_id' => $e->user->id,
                    'reason'  => $e->switchReason ?: null,
                ])
                ->listen(ImpersonationEnded::class, 'user.impersonation_ended', fn ($e) => [
                    'user_id' => $e->originalUser->id,
                ]),
        ]),
];
