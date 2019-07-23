<?php

namespace App\Planfix\Pagination;

use Iterator;
use Countable;
use Closure;
use App\Planfix\Entity\Collection;

class Paginator implements Iterator, Countable 
{
	/**
	 * @var \Closure
	 */
	protected $callback;

	/**
	 * @var array
	 */
	protected $items = [];

	/**
	 * @var integer
	 */
	protected $totalCount = 0;

	/**
	 * @var integer
	 */
	protected $currentPage = 0;

	/**
	 * @var integer
	 */
	protected $currentPosition = 0;

	/**
	 * {@inheritDocs}
	 */
	public function __sleep()
	{
		return [
			'items',
			'totalCount',
			'currentPage',
			'currentPosition',
		];
	}

	/**
	 * Задать функцию для загрузки списка элементов
	 * 
	 * @param \Closure $callback 
	 */
	public function setCallback($callback)
	{
		$this->callback = $callback;

		return $this;
	}

	/**
	 * Функция для загрузки списка элементов
	 * 
	 * @return \Closure
	 */
	public function getCallback()
	{
		return $this->callback;
	}

	/**
	 * Текущая страница
	 * 
	 * @return integer
	 */
	public function getCurrentPage()
	{
		return $this->currentPage;
	}

	/**
	 * Загрузить страницу
	 * 
	 * @param integer $page
	 */
	protected function fetch($page)
	{
		$this->currentPage = $page;
		if (is_callable($this->callback)) {
			$collection = call_user_func($this->callback, $page);
		} else {
			return;
		}

		if (! ($collection instanceof Collection)) {
			return;
		}

		$this->totalCount = $collection->totalCount;
		$this->items = array_merge($this->items, $collection->items);
	}

	/**
	 * {@inheritDocs}
	 */
	public function count()
	{
		return $this->totalCount;
	}

	/**
	 * {@inheritDocs}
	 */
	public function current()
	{
		return $this->items[$this->currentPosition];
	}

	/**
	 * {@inheritDocs}
	 */
	public function key()
	{
		return $this->currentPosition;
	}

	/**
	 * {@inheritDocs}
	 */
	public function next()
	{
		++$this->currentPosition;
	}

	/**
	 * {@inheritDocs}
	 */
	public function rewind()
	{
		$this->currentPosition = 0;
	}

	/**
	 * {@inheritDocs}
	 */
	public function valid()
	{
		$count = count($this->items);

		if ($this->currentPosition >= $count) {
			if ($this->currentPage == 0 || $this->totalCount > $count) {
				$this->fetch($this->currentPage + 1);
			}
		}

		return isset($this->items[$this->currentPosition]);
	}

	/**
	 * Все элементы
	 * 
	 * @return array
	 */
	public function all()
	{
		while($this->currentPage == 0 || $this->totalCount > count($this->items)) {
			$this->fetch($this->currentPage + 1);
		}

		return $this->items;
	}
}