<?php

namespace Flagrow\Impersonate\Controllers;

use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookies;
use Flarum\Api\Serializer\UserSerializer;
use Flarum\Http\CookieFactory;
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
    protected $cookies;

    public $serializer = UserSerializer::class;

    public function __construct(SessionAuthenticator $authenticator, Rememberer $rememberer, CookieFactory $cookies)
    {
        $this->authenticator = $authenticator;
        $this->rememberer = $rememberer;
        $this->cookies = $cookies;
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

        $response = $this->rememberer->forget(new JsonResponse(true));

        $dummyCookie = $this->cookies->make('dummy');

        // Fix the Remember::forget() method not using the cookie factory
        // And as such having the wrong cookie path
        // This is already fixed in beta8
        foreach (SetCookies::fromResponse($response)->getAll() as $cookie) {
            // Only alter expired cookies
            if ($cookie->getExpires() < time()) {
                $response = FigResponseCookies::set($response, $cookie
                    ->withPath($dummyCookie->getPath())
                    ->withSecure($dummyCookie->getSecure())
                    ->withHttpOnly($dummyCookie->getHttpOnly())
                );
            }
        }

        return $response;
    }
}
