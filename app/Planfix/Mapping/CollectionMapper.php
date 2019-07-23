<?php

namespace App\Planfix\Mapping;

use App\Planfix\Entity\Entity;
use App\Planfix\Entity\Collection;

class CollectionMapper extends Mapper
{
	/**
	 * Конструктор
	 * 
	 * @param \Closure|array $source 
	 * @param string $path 
	 */
	public function __construct($source, $path)
	{
		$mapper = new Mapper($source);

		$totalCount = $mapper->get('meta.totalCount', 0);
		$count = $mapper->get('meta.count', $totalCount);
		$items = $mapper->get($path, []);

		if ($count == 1) {
			$items = [$items];
		}

		parent::__construct(
			compact('totalCount', 'count', 'items')
		);
	}

	/**
	 * {@inheritDocs}
	 */
	public function transform($class)
	{
		$totalCount = $this->get('totalCount');
		$count = $this->get('count');
		$items = $this->get('items');

		return new Collection([
			'totalCount' => $totalCount,
			'count' => $count,
			'items' => array_map(function($item) use ($class) {
				return is_a($class, Entity::class, true) ? new $class($item) : $item;
			}, (array)$items)
		]);
	}
}