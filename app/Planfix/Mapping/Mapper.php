<?php

namespace App\Planfix\Mapping;

use App\Planfix\Entity\Entity;

class Mapper
{
	/**
	 * @var array
	 */
	protected $source;

	/**
	 * Конструктор
	 * 
	 * @param \Closure|array $source 
	 */
	public function __construct($source)
	{
		if (is_callable($source)) {
			$source = call_user_func($source);
		}

		$this->source = $source;
	}

	/**
	 * Исходный массив
	 * 
	 * @return array
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * Параметр
	 * 
	 * @param string $path 
	 * @param mixed $default 
	 * @return mixed
	 */
	public function get($path, $default = null)
	{
		$path = explode('.', $path);
		$value = $this->source;

		while ($value && $i = array_shift($path)) {
			$value = isset($value[$i]) ? $value[$i] : null;
		}

		return $value ? : $default;
	}

	/**
	 * Преобразование в объект
	 * 
	 * @param string $class 
	 * @return \App\Planfix\Entity|null
	 */
	public function transform($class)
	{
		if ($this->source) {
			if (is_a($class, Entity::class, true)) {
				return new $class($this->source);
			}
		}

		return null;
	}
}