<?php

namespace App\Contracts;

use Slim\Http\Request;
use Slim\Http\Response;
use Interop\Container\ContainerInterface;

abstract class Middleware
{
    use ContainerInside;

    /**
     * Controller constructor
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Middleware
     *
     * @param \Slim\Http\Request $request
     * @param \Slim\Http\Response $response
     * @param \Closure $next
     * @return \Slim\Http\Response
     */
    abstract public function __invoke(Request $request, Response $response, $next);
}