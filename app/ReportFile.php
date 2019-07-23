<?php

namespace App;

class ReportFile
{
	/**
	 * @var int
	 */
	public $year = 0;

	/**
	 * @var int
	 */
	public $month = 0;

	/**
	 * @var int
	 */
	public $project_id = 0;

	/**
	 * @var int
	 */
	public $name;

	/**
	 * Конструктор
	 */
	protected function __construct()
	{
		// 
	}

	/**
	 * Путь к файлу
	 * 
	 * @return string
	 */
	public function getPath()
	{
		return storage_path() . sprintf(
			'/%d/%d/%d/%s',
			$this->year,
			$this->month,
			$this->project_id,
			$this->name
		);
	}

	/**
	 * Наличие файла на сервере
	 * 
	 * @return boolean
	 */
	public function isExists()
	{
		return file_exists(
			$this->getPath()
		);
	}

	/**
	 * Размер файла
	 * 
	 * @return integer
	 */
	public function getSize()
	{
		return $this->isExists() ? filesize(
			$this->getPath()
		) : 0;
	}

	/**
	 * Mime тип файла
	 * 
	 * @return string
	 */
	public function getMimeType()
	{
		if ($this->isExists()) {
			return mime_content_type($this->getPath());
		}

		return null;
	}

	/**
	 * Время изменения файла
	 * 
	 * @return string
	 */
	public function getCreatedAt()
	{
		return $this->isExists() ? date('d.m.Y H:i', filemtime($this->getPath())) : null;
	}

	/**
	 * Вывести содержимое файла
	 */
	public function read()
	{
		if ($this->isExists()) {
			readfile(
				$this->getPath()
			);
		}
	}

	/**
	 * Создать файл для заданного отчета
	 * 
	 * @param \App\Report $report 
	 * @return static
	 */
	public static function create(Report $report)
	{
		$self = new static();
		$self->project_id = $report->project->id;
		$self->project_name = base64urlencode($report->project->title);
		$self->year = $report->year;
		$self->month = $report->month;

		$self->name = sprintf(
			'Отчет %s - план на %s - SEO %s.docx',
			mb_strtolower(get_month($self->month)),
			mb_strtolower(get_month($self->month + 1)),
			$report->project->title ? : $self->project_id
		);

		return $self;
	}

	/**
	 * Поиск файла по заданным параметрам
	 * 
	 * @param array $attributes 
	 * @return static|null
	 */
	public static function find(array $attributes)
	{
		$self = new static();

		foreach ($attributes as $key => $value) {
			if (property_exists($self, $key)) {
				$self->{$key} = $value;
			}
		}

		if ($self->isExists()) {
			return $self;
		}

		return null;
	}
}