<?php

namespace Flagrow\Impersonate\Listeners;

use Flagrow\Impersonate\Controllers\LoginController;
use Flarum\Event\ConfigureApiRoutes;
use Illuminate\Contracts\Events\Dispatcher;

class AddApiRoutes
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(ConfigureApiRoutes::class, [$this, 'routes']);
    }

    public function routes(ConfigureApiRoutes $routes)
    {
        $routes->post(
            '/flagrow/impersonate/{id:[0-9]+}',
            'flagrow.impersonate.api.login',
            LoginController::class
        );
    }
}
