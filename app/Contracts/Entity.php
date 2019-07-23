<?php

namespace App\Contracts;

class Entity
{
	/**
	 * Constructor
	 * 
	 * @param array $attributes 
	 */
	public function __construct($attributes = [])
	{
		foreach ($attributes as $key => $value) {
			if (property_exists($this, $key)) {
				$this->{$key} = $value;
			}
		}
	}
}