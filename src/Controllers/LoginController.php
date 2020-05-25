<?php

/*
 * This file is part of fof/impersonate.
 *
 * Copyright (c) 2020 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Impersonate\Controllers;

use Flarum\Api\Serializer\UserSerializer;
use Flarum\Foundation\ValidationException;
use Flarum\Http\Rememberer;
use Flarum\Http\SessionAuthenticator;
use Flarum\User\AssertPermissionTrait;
use Flarum\User\User;
use FoF\Impersonate\Events\Impersonated;
use Illuminate\Events\Dispatcher;
use Illuminate\Contracts\Session\Session;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoginController implements RequestHandlerInterface
{
    use AssertPermissionTrait;

    protected $authenticator;
    protected $rememberer;
    protected $bus;

    public $serializer = UserSerializer::class;

    public function __construct(SessionAuthenticator $authenticator, Rememberer $rememberer, Dispatcher $bus)
    {
        $this->authenticator = $authenticator;
        $this->rememberer = $rememberer;
        $this->bus = $bus;
    }

    /**
     * Handle the request and return a response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Flarum\User\Exception\PermissionDeniedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = $request->getAttribute('actor');

        $requestBody = $request->getParsedBody();
        $requestData = $requestBody['data']['attributes'];

        $id = $requestData['userId'];
        $reason = $requestData['reason'];

        if (app('flarum.settings')->get('fof-impersonate.require_reason', false) && $reason === '') {
            throw new ValidationException([
                'error' => app('translator')->trans('fof-impersonate.forum.modal.placeholder_required')
            ]);
        }

        /**
         * @var $user User
         */
        $user = User::findOrFail($id);

        $this->assertCan($actor, 'fofCanImpersonate', $user);

        /**
         * @var $session Session
         */
        $session = $request->getAttribute('session');
        $this->authenticator->logIn($session, $user->id);

        $this->bus->dispatch(new Impersonated($actor, $user, $reason));

        return $this->rememberer->forget(new JsonResponse(
            [
                'data' => [
                    'type' => 'impersonate',
                    'attributes' => [
                        'success' => true
                    ]
                ]
            ]
        ));
    }
}
