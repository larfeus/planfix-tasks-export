<?php

namespace App\Planfix\Entity;

class TaskStatus extends Entity
{
	/**
	 * {@inheritDocs}
	 */
	public function casts()
	{
		return [
			'id' 					=> 'integer',
			'name' 					=> 'string',
			'isActive' 				=> 'boolean',
			'hasDeadline' 			=> 'boolean',
		];
	}
}