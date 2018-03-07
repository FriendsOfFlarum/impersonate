<?php

namespace Flagrow\Impersonate;

use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    $events->subscribe(Listeners\AddApiRoutes::class);
    $events->subscribe(Listeners\AddUserAttributes::class);
    $events->subscribe(Listeners\Assets::class);

    $events->subscribe(Access\UserPolicy::class);
};
