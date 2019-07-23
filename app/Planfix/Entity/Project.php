<?php

namespace App\Planfix\Entity;

class Project extends Entity
{
	/**
	 * {@inheritDocs}
	 */
	public function casts()
	{
		return [
			'id' 				=> 'integer',
			'title' 			=> 'string',
			'description' 		=> 'object',
			'owner' 			=> 'object',
			'client' 			=> 'object',
			'template' 			=> 'object',
			'group' 			=> 'object',
			'status' 			=> 'string',
			'hidden' 			=> 'array',
			'hasEndDate' 		=> 'boolean',
			'taskCount' 		=> 'integer',
			'isOverdued' 		=> 'boolean',
			'isCloseToDeadline' => 'boolean',
			'beginDate' 		=> 'string',
			'parent' 			=> 'object',
			'customData' 		=> 'array',
		];
	}
}