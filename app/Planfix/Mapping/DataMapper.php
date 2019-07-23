<?php

namespace App\Planfix\Mapping;

class DataMapper extends Mapper
{
	/**
	 * Конструктор
	 * 
	 * @param \Closure|array $source 
	 * @param string $path 
	 */
	public function __construct($source, $path)
	{
		parent::__construct(
			(new Mapper($source))
				->get($path)
		);
	}
}