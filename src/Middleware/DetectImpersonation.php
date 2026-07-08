<?php

/*
 * This file is part of fof/impersonate.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Impersonate\Middleware;

use FoF\Impersonate\Impersonation;
use Illuminate\Contracts\Session\Session;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * Records whether the current request's session is impersonating, before
 * AuthenticateWithSession runs and updates the actor's last_seen_at.
 */
class DetectImpersonation implements MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        /**
         * @var Session|null $session
         */
        $session = $request->getAttribute('session');

        $impersonatedUserId = $session?->get(Impersonation::IMPERSONATED_USER_SESSION_KEY);

        Impersonation::$impersonatedUserId = $impersonatedUserId ? (int) $impersonatedUserId : null;

        try {
            return $handler->handle($request);
        } finally {
            Impersonation::$impersonatedUserId = null;
        }
    }
}
