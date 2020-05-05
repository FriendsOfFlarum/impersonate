<?php

/*
 * This file is part of fof/impersonate.
 *
 * Copyright (c) 2020 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Impersonate;

use Flarum\Extend;
use Illuminate\Contracts\Events\Dispatcher;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    new Extend\Locales(__DIR__ . '/resources/locale'),

    (new Extend\Routes('api'))
        ->post('/impersonate/{id:[0-9]+}', 'fof.impersonate.api.login', Controllers\LoginController::class),
        
    function (Dispatcher $events) {
        $events->subscribe(Listeners\AddUserAttributes::class);

        $events->subscribe(Access\UserPolicy::class);
    },
];
