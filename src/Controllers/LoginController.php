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

use Flarum\Extension\ExtensionManager;
use Flarum\Foundation\ValidationException;
use Flarum\Http\AccessToken;
use Flarum\Http\RememberAccessToken;
use Flarum\Http\Rememberer;
use Flarum\Http\RequestUtil;
use Flarum\Http\SessionAccessToken;
use Flarum\Http\SessionAuthenticator;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use FoF\Impersonate\Events\Impersonated;
use FoF\Impersonate\Impersonation;
use Illuminate\Contracts\Session\Session;
use Illuminate\Events\Dispatcher;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginController implements RequestHandlerInterface
{
    public function __construct(
        protected SessionAuthenticator $authenticator,
        protected Rememberer $rememberer,
        protected Dispatcher $bus,
        protected SettingsRepositoryInterface $settings,
        protected TranslatorInterface $translator,
        protected ExtensionManager $extensions
    ) {
    }

    /**
     * Handle the request and return a response.
     *
     * @param ServerRequestInterface $request
     *
     * @throws \Flarum\User\Exception\PermissionDeniedException
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /**
         * @var User $actor
         */
        $actor = RequestUtil::getActor($request);

        $requestBody = $request->getParsedBody();
        $requestData = $requestBody['data']['attributes'];

        $id = $requestData['userId'];
        $reason = $requestData['reason'] ?? '';

        if ($this->reasonSupported() && (bool) $this->settings->get('fof-impersonate.require_reason') && empty($reason)) {
            throw new ValidationException([
                'error' => $this->translator->trans('fof-impersonate.forum.modal.placeholder_required'),
            ]);
        }

        /**
         * @var User $user
         */
        $user = User::findOrFail($id);

        $actor->assertCan('fofCanImpersonate', $user);

        /**
         * @var Session $session
         */
        $session = $request->getAttribute('session');

        // logIn() replaces the session's token without deleting it. Capture it so it
        // can be removed instead of lingering in the database, and so a remember
        // login can be restored with a fresh token on return.
        $previousTokenId = $session->get('access_token');
        $previousToken = $previousTokenId ? AccessToken::findValid($previousTokenId) : null;

        $this->authenticator->logIn($session, SessionAccessToken::generate($user->id));

        // Session attributes survive logIn()'s regeneration, so the markers set here
        // (or by an earlier impersonation in a chain) persist until logout or return.
        if ((int) $session->get(Impersonation::ORIGINAL_USER_SESSION_KEY) === $user->id) {
            // Impersonated their way back to the account that started the chain.
            $session->forget(Impersonation::ORIGINAL_USER_SESSION_KEY);
            $session->forget(Impersonation::IMPERSONATED_USER_SESSION_KEY);
            $session->forget(Impersonation::ORIGINAL_REMEMBER_SESSION_KEY);
        } else {
            if (!$session->has(Impersonation::ORIGINAL_USER_SESSION_KEY)) {
                $session->put(Impersonation::ORIGINAL_USER_SESSION_KEY, $actor->id);
                $session->put(Impersonation::ORIGINAL_REMEMBER_SESSION_KEY, $previousToken instanceof RememberAccessToken);
            }

            $session->put(Impersonation::IMPERSONATED_USER_SESSION_KEY, $user->id);
        }

        $previousToken?->delete();

        $this->bus->dispatch(new Impersonated($actor, $user, $reason));

        return $this->rememberer->forget(new JsonResponse(
            [
                'data' => [
                    'type'       => 'impersonate',
                    'attributes' => [
                        'success' => true,
                    ],
                ],
            ]
        ));
    }

    /**
     * A reason can only be required when an extension that records it is enabled:
     * fof/moderator-notes attaches it to the user, flarum/audit stores it in the log.
     */
    protected function reasonSupported(): bool
    {
        return $this->extensions->isEnabled('fof-moderator-notes') || $this->extensions->isEnabled('flarum-audit');
    }
}
