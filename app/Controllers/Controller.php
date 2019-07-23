<?php

namespace App\Controllers;

use Slim\Views\Twig;
use Slim\Http\Request;
use Slim\Http\Response;
use Interop\Container\ContainerInterface;

abstract class Controller
{
    /**
     * Slim Container
     *
     * @var \Interop\Container\ContainerInterface
     */
    protected $container;

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
     * Get Slim Container
     *
     * @return \Interop\Container\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * Get Service From Container
     *
     * @param string $service
     * @return mixed
     */
    protected function getService($service)
    {
        return $this->container->{$service};
    }

    /**
     * Get Request
     *
     * @return \Slim\Http\Request
     */
    protected function getRequest()
    {
        return $this->container->request;
    }

    /**
     * Get Response
     *
     * @return \Slim\Http\Response
     */
    protected function getResponse()
    {
        return $this->container->response;
    }

    /**
     * Get Twig Engine
     *
     * @return \Slim\Views\Twig
     */
    protected function getView()
    {
        return $this->container->view;
    }

    /**
     * Render view
     *
     * @param string $template
     * @param array $data
     * @return string
     */
    protected function render($template, $data = [])
    {
        try {
            $response = $this->getView()->render($this->getResponse(), $template, $data);
        } catch (\Exception $e) {
            if ($previous = $e->getPrevious()) {
                throw $previous;
            } else {
                throw $e;
            }
        }

        return $response;
    }

    /**
     * Редирект на указанный адрес
     * 
     * @param string $url 
     * @return \Slim\Http\Response
     */
    protected function redirect($url)
    {
        return $this->getResponse()
            ->withRedirect(
                $url
            );
    }

    /**
     * Редирект на указанный роут
     * 
     * @param string $name
     * @param array $params 
     * @return \Slim\Http\Response
     */
    protected function route($name, $params = [])
    {
        return $this->redirect(
            $this->getService('router')->pathFor($name, $params)
        );
    }

    /**
     * Редирект на предыдущий роут
     * 
     * @return \Slim\Http\Response
     */
    protected function back()
    {
        return $this->redirect(
            ($referrer = $this->getRequest()->getHeader('HTTP_REFERER'))
                ? array_pop($referrer) : $this->getRequest()->getUri()->getPath()
        );
    }
}