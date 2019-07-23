<?php

namespace App\Contracts;

trait ContainerInside
{
    /**
     * Slim Container
     *
     * @var \Interop\Container\ContainerInterface
     */
	protected $container;

    /**
     * Container
     * 
     * @return \Interop\Container\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Service
     * 
     * @param string $name 
     * @return mixed
     */
    public function getService($name)
    {
        return $this->container->{$name};
    }
}