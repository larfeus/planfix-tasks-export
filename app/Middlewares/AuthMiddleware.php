<?php

namespace App\Middlewares;

use Slim\Http\Request;
use Slim\Http\Response;
use App\Contracts\Middleware;
use App\Exceptions\UnauthorizedException;

class AuthMiddleware extends Middleware
{
    /**
     * Middleware
     *
     * @param \Slim\Http\Request $request
     * @param \Slim\Http\Response $response
     * @param \Closure $next
     * @return \Slim\Http\Response
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $auth = $this->getService('auth');

        if ($auth->guest()) {
            throw new UnauthorizedException;
        }

        $view = $this->getService('view');
        $view->getEnvironment()->addGlobal('user', $auth->getUser());

        return $next($request, $response);
    }
}