<?php

namespace App\Planfix\Entity;

abstract class Entity
{
	/**
	 * Конструктор
	 * 
	 * @param array $attributes 
	 */
	public function __construct($attributes = [])
	{
		$casts = $this->casts();

		foreach ($attributes as $key => $value) {
			if (array_key_exists($key, $casts)) {
				$this->{$key} = $this->castAttribute($value, $casts[$key]);
			}
		}
	}

	/**
	 * Преобразование значения атрибута
	 * 
	 * @param mixed $value 
	 * @param mixed $settings 
	 * @param mixed $default 
	 * @return mixed
	 */
	protected function castAttribute($value, $settings, $default = null)
	{
		$settings = (array)$settings + [null, null];
		list($type, $subtype) = $settings;

		if (empty($value)) {
			$value = $default;
		}

		switch($type) {
			case 'number':
			case 'integer':
			case 'int':
				$value = intval($value);
				break;
			case 'float':
			case 'real':
			case 'double':
				$value = floatval($value);
				break;
			case 'string':
			case 'str':
				$value = (string)$value;
				break;
			case 'boolean':
			case 'bool':
				$value = boolval($value);
				break;
			case 'object':
				$value = (object)$value;
				break;
			case 'array':
				$value = array_map(function($item) use ($subtype) {
					return $this->castAttribute($item, $subtype);
				}, (array)$value);
				break;
			default:
				if (is_callable($settings, true)) {
					$value = $settings($value);
				}
				elseif (class_exists($type) && is_a($type, __CLASS__, true)) {
					$value = new $type($value);
				}
		}

		return $value;
	}

	/**
	 * Типизация атрибутов
	 * 
	 * @return array
	 */
	abstract function casts();
}