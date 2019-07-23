<?php

namespace App\Planfix\Entity;

class Collection extends Entity
{
	/**
	 * {@inheritDocs}
	 */
	public function casts()
	{
		return [
			'totalCount' 	=> 'integer',
			'count' 		=> 'integer',
			'items' 		=> 'array',
		];
	}
}