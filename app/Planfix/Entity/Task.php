<?php

namespace App\Planfix\Entity;

class Task extends Entity
{
	/**
	 * {@inheritDocs}
	 */
	public function casts()
	{
		return [
			'id' 					=> 'integer',
			'title' 				=> 'string',
			'description' 			=> 'object',
			'importance' 			=> 'string',
			'status' 				=> 'integer',
			'statusSet' 			=> 'integer',
			'checkResult' 			=> 'boolean',
			'owner' 				=> 'object',
			'parent' 				=> 'object',
			'template' 				=> 'object',
			'project' 				=> 'object',
			'client' 				=> 'object',
			'beginDateTime' 		=> 'string',
			'general' 				=> 'integer',
			'isOverdued' 			=> 'boolean',
			'isCloseToDeadline' 	=> 'boolean',
			'isNotAcceptedInTime' 	=> 'boolean',
			'starred' 				=> 'boolean',
			'customData'			=> [$this, 'castCustomData'],
		];
	}

	/**
	 * Заключение
	 * 
	 * @return mixed
	 */
	public function getConclusion()
	{
		return $this->getCustomDataValue('31754');
	}

	/**
	 * Позиции сайта
	 * 
	 * @return mixed
	 */
	public function getSitePositions()
	{
		return $this->getCustomDataValue('31758');
	}

	/**
	 * Результат аудита
	 * 
	 * @return mixed
	 */
	public function getResultOfAudit()
	{
		return $this->getCustomDataValue('31756');
	}

	/**
	 * Итог проработки
	 * 
	 * @return mixed
	 */
	public function getResultOfProcessing()
	{
		return $this->getCustomDataValue('31750');
	}

	/**
	 * Story points
	 * 
	 * @return mixed
	 */
	public function getStoryPoints()
	{
		return round(($this->getCustomDataValue('31690') ? : 0) / 6, 2);
	}

	/**
	 * Значение пользовательского поля
	 * 
	 * @param integer $id
	 * @return mixed
	 */
	public function getCustomDataValue($id)
	{
		if ($this->customData) {
			foreach ($this->customData as $item) {
				if ($item->field['id'] == $id) {
					return is_string($item->value) ? strip_tags($item->value) : '';
				}
			}
		}

		return '';
	}

	/**
	 * Преобразование пользовательских полей
	 * 
	 * @param mixed $value 
	 * @return mixed
	 */
	public function castCustomData($value)
	{
		$value = isset($value['customData']) ? $value['customData'] : [];

		return $this->castAttribute($value, ['array', 'object'], []);
	}
}