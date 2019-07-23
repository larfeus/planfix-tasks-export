<?php

namespace App\Middlewares;

use Slim\Http\Request;
use Slim\Http\Response;
use App\Contracts\Middleware;

class ExampleMiddleware extends Middleware
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
        $response->getBody()->write('-- BEFORE --<br><br>');

        $response = $next($request, $response);

        $response->getBody()->write('<br><br>-- AFTER --');

        return $response;
    }
}