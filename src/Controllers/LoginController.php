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
use Flarum\Extension\ExtensionManager;
use Flarum\Http\Rememberer;
use Flarum\Http\SessionAuthenticator;
use Flarum\User\AssertPermissionTrait;
use Flarum\User\User;
use FoF\Impersonate\Events\Impersonated;
use FoF\ModeratorNotes\Command\CreateModeratorNote;
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
    protected $extensions;
    protected $bus;

    public $serializer = UserSerializer::class;

    public function __construct(SessionAuthenticator $authenticator, Rememberer $rememberer, ExtensionManager $extensions, Dispatcher $bus)
    {
        $this->authenticator = $authenticator;
        $this->rememberer = $rememberer;
        $this->extensions = $extensions;
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
        $id = array_get($request->getQueryParams(), 'id');

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

        $this->bus->dispatch(new Impersonated($actor, $user));

        return $this->rememberer->forget(new JsonResponse(true));
    }
}
