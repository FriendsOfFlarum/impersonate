<?php

namespace Flagrow\Impersonate;

use Flarum\Extend;
use Illuminate\Contracts\Events\Dispatcher;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js'),
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),
    new Extend\Locales(__DIR__ . '/resources/locale'),
    (new Extend\Routes('api'))
        ->post('/flagrow/impersonate/{id:[0-9]+}', 'flagrow.impersonate.api.login', Controllers\LoginController::class),
    function (Dispatcher $events) {
        $events->subscribe(Listeners\AddUserAttributes::class);

        $events->subscribe(Access\UserPolicy::class);
    },
];
