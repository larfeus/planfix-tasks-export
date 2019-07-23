<?php

namespace App\Services;

use App\Contracts\ContainerInside;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\GoogleDriveAuthorizationException;
use Slim\Handlers\Error;
use Slim\Http\StatusCode;
use UnexpectedValueException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Interop\Container\ContainerInterface;

class ErrorHandlerService extends Error
{
    use ContainerInside;

    /**
     * @var \Interop\Container\ContainerInterface
     */
    protected $container;

    /**
     * ErrorHandler constructor
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct(env('APP_DEBUG', false));
    }

    /**
     * Invoke error handler
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param \Exception             $exception The caught Exception object
     *
     * @return ResponseInterface
     * @throws UnexpectedValueException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, \Exception $exception)
    {
        if ($exception instanceof GoogleDriveAuthorizationException) {

            $url = $this->getService('googledrive')->getAuthUrl();

            return $response->withRedirect($url, StatusCode::HTTP_MOVED_PERMANENTLY);
        }

        if ($exception instanceof UnauthorizedException) {

            $url = $this->getService('router')->pathFor('login');

            return $response->withRedirect($url, StatusCode::HTTP_MOVED_PERMANENTLY);
        }

        $this->getService('logger')->critical($exception->getMessage());

        return parent::__invoke($request, $response, $exception);
    }
}