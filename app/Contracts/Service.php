<?php

namespace App\Contracts;

use Interop\Container\ContainerInterface;

abstract class Service
{
	use ContainerInside;
	
	/**
	 * Конструктор
	 * 
	 * @param \Interop\Container\ContainerInterface $container 
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;		
	}
}