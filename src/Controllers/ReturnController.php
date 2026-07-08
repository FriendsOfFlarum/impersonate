<?php

/*
 * This file is part of fof/impersonate.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Impersonate\Controllers;

use Flarum\Http\AccessToken;
use Flarum\Http\RememberAccessToken;
use Flarum\Http\Rememberer;
use Flarum\Http\RequestUtil;
use Flarum\Http\SessionAccessToken;
use Flarum\Http\SessionAuthenticator;
use Flarum\User\Exception\PermissionDeniedException;
use Flarum\User\User;
use FoF\Impersonate\Events\ImpersonationEnded;
use FoF\Impersonate\Impersonation;
use Illuminate\Contracts\Session\Session;
use Illuminate\Events\Dispatcher;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ReturnController implements RequestHandlerInterface
{
    public function __construct(
        protected SessionAuthenticator $authenticator,
        protected Rememberer $rememberer,
        protected Dispatcher $bus
    ) {
    }

    /**
     * Switch the session back to the user who started impersonating.
     *
     * Deliberately does not re-check the fofCanImpersonate policy: returning to
     * your own account must always work, even if the permission was revoked
     * mid-session. The marker was written server-side at a moment permission
     * was verified.
     *
     * @throws PermissionDeniedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);

        /**
         * @var Session|null $session
         */
        $session = $request->getAttribute('session');

        $originalUserId = $session?->get(Impersonation::ORIGINAL_USER_SESSION_KEY);

        // Require a live impersonated session: a guest whose session token has
        // expired must not be able to enter the original account without credentials.
        if ($actor->isGuest() || !$originalUserId) {
            throw new PermissionDeniedException();
        }

        // Only the account the impersonator actually switched to may return. Core's
        // POST /login preserves session attributes, so after a credentialed login into
        // a different account the markers are stale — clear them instead of honoring them.
        if ($actor->id !== (int) $session->get(Impersonation::IMPERSONATED_USER_SESSION_KEY)) {
            $session->forget(Impersonation::ORIGINAL_USER_SESSION_KEY);
            $session->forget(Impersonation::IMPERSONATED_USER_SESSION_KEY);

            throw new PermissionDeniedException();
        }

        /**
         * @var User|null $originalUser
         */
        $originalUser = User::query()->find($originalUserId);

        if (!$originalUser) {
            // The original account no longer exists; end the session entirely.
            $this->authenticator->logOut($session);

            return $this->success();
        }

        // logIn() replaces the session's token without deleting it, so remove the
        // impersonation token explicitly before switching back.
        AccessToken::findValid($session->get('access_token'))?->delete();

        // Restore an equivalent session to the one impersonation replaced: the
        // original remember token was deleted then, so mint a fresh one now.
        $token = $session->get(Impersonation::ORIGINAL_REMEMBER_SESSION_KEY)
            ? RememberAccessToken::generate($originalUser->id)
            : SessionAccessToken::generate($originalUser->id);

        $this->authenticator->logIn($session, $token);

        // The markers survive logIn()'s session regeneration; clear them explicitly.
        $session->forget(Impersonation::ORIGINAL_USER_SESSION_KEY);
        $session->forget(Impersonation::IMPERSONATED_USER_SESSION_KEY);
        $session->forget(Impersonation::ORIGINAL_REMEMBER_SESSION_KEY);

        $this->bus->dispatch(new ImpersonationEnded($actor, $originalUser));

        $response = $this->success();

        if ($token instanceof RememberAccessToken) {
            $response = $this->rememberer->remember($response, $token);
        }

        return $response;
    }

    protected function success(): ResponseInterface
    {
        return new JsonResponse([
            'data' => [
                'type'       => 'impersonate',
                'attributes' => [
                    'success' => true,
                ],
            ],
        ]);
    }
}
