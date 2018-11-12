<?php

namespace Flagrow\Impersonate\Controllers;

use Flarum\Api\Serializer\UserSerializer;
use Flarum\Http\Rememberer;
use Flarum\Http\SessionAuthenticator;
use Flarum\User\AssertPermissionTrait;
use Flarum\User\User;
use Illuminate\Contracts\Session\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

class LoginController implements RequestHandlerInterface
{
    use AssertPermissionTrait;

    protected $authenticator;
    protected $rememberer;

    public $serializer = UserSerializer::class;

    public function __construct(SessionAuthenticator $authenticator, Rememberer $rememberer)
    {
        $this->authenticator = $authenticator;
        $this->rememberer = $rememberer;
    }

    /**
     * Handle the request and return a response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Flarum\User\Exception\PermissionDeniedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = array_get($request->getQueryParams(), 'id');

        /**
         * @var $user User
         */
        $user = User::findOrFail($id);

        $this->assertCan($request->getAttribute('actor'), 'flagrowCanImpersonate', $user);

        /**
         * @var $session Session
         */
        $session = $request->getAttribute('session');
        $this->authenticator->logIn($session, $user->id);

        return $this->rememberer->forget(new JsonResponse(true));
    }
}
