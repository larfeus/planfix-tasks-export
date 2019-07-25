<?php

namespace App;

use App\Planfix\Entity\Project;
use App\Planfix\Entity\Task;

class Report
{
	const TASK_TITLE 		= 1;
	const TASK_COMMENT 		= 2;
	const TASK_STORY 		= 3;
	const TASK_STATUS 		= 4;
	const TASK_AUDIT 		= 5;
	const TASK_POSITIONS	= 6;

	/**
	 * @var array
	 */
	public $columns = [
		self::TASK_TITLE 	=> 'Задача',
		self::TASK_COMMENT 	=> 'Комментарии',
		self::TASK_STORY 	=> 'Трудозатраты',
		self::TASK_STATUS 	=> 'Статус'
	];

	/**
	 * @var array
	 */
	public $additional = [
		self::TASK_AUDIT 	=> 'План подготовлен с учетом данных аудита',
		self::TASK_POSITIONS => 'Ссылка на гостевой доступ системы отслеживания позиций',
	];

	/**
	 * @var \App\Planfix\Entity\Project
	 */
	public $project;

	/**
	 * @var integer
	 */
	public $month = 7;

	/**
	 * @var integer
	 */
	public $year = 2019;

	/**
	 * @var array
	 */
	public $current_tasks = [];

	/**
	 * @var array
	 */
	public $current_columns = [
		self::TASK_TITLE,
		self::TASK_COMMENT,
		self::TASK_STATUS,
	];

	/**
	 * @var array
	 */
	public $next_tasks = [];

	/**
	 * @var array
	 */
	public $next_columns = [
		self::TASK_TITLE,
		self::TASK_COMMENT,
	];

	/**
	 * @var array
	 */
	public $next_additional = [
		self::TASK_AUDIT,
	];

	/**
	 * @var array
	 */
	public $question_tasks = [];

	/**
	 * @var array
	 */
	public $question_columns = [
		self::TASK_TITLE,
	];

	/**
	 * @var \PhpOffice\PhpWord\PhpWord
	 */
	protected $phpword;

	/**
	 * Развернуть зависимые задачи в одинарный список
	 * 
	 * @param array $tasks 
	 * @return array
	 */
	protected function flatten(array $tasks)
	{
		$flatten = [];

		foreach ($tasks as $task) {
			$flatten = array_merge($flatten, [$task], $this->flatten($task->tasks));
		}

		return $flatten;
	}

	/**
	 * Только задачи, не имеющие подзадач
	 * 
	 * @param array $tasks 
	 * @return array
	 */
	protected function terminal(array $tasks)
	{
		return array_filter($tasks, function($task) {

			if (preg_match('/^\d+\s+неделя\:?$/i', trim($task->title))) {
				return false;
			}

			return count($task->tasks) == 0;
		});
	}

	/**
	 * Сгенерировать отчет
	 */
	public function generate()
	{
		$phpword = new \PhpOffice\PhpWord\PhpWord();
		$section = $phpword->addSection();

		\PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);

		// Стили
		$primaryFontStyle = [
			'name' => 'Arial', 
			'size' => 14,
		];

		$subFontStyle = [
			'name' => 'Arial', 
			'size' => 10,
		];

		$secondaryFontStyle = [
			'name' => 'Arial', 
			'size' => 11,
			'bold' => true,
		];

		$textFontStyle = [
			'name' => 'Arial', 
			'size' => 11,
		];

		$textFontWithoutSpace = [
			'spaceAfter' => 0,
		];

		// Заголовок
		$text = sprintf('Отчет по SEO / план %s', $this->project->title);
		$section->addText($text, $primaryFontStyle, $textFontWithoutSpace);

		$text = sprintf('Отчетный период: %02d.%04d', $this->month, $this->year);
		$section->addText($text, $subFontStyle);

		// Первая таблица (план за текущий месяц)
		$section->addTextBreak();
		$section->addText('Отчет по выполненным работам:', $secondaryFontStyle);
		$section->addTextBreak();
		$data = $this->getTableData($this->current_tasks, $this->current_columns);
		$this->addTable($section, $data);

		// Вторая таблица (план на следующий месяц)
		$section->addTextBreak();
		$section->addText('План работы нового месяца:', $secondaryFontStyle);
		$section->addTextBreak();
		$data = $this->getTableData($this->next_tasks, $this->next_columns);
		$this->addTable($section, $data);

		// Ремарка
		if (count($this->next_additional) > 0) {
			$section->addTextBreak();
		}

		if (in_array(self::TASK_AUDIT, $this->next_additional)) {
			$text = implode(', ',
				array_map(function($task) {
					return $this->decodeEntities($task->getResultOfAudit());
				}, $this->next_tasks)
			);
			$text = sprintf('План подготовлен с учетом данных аудита: %s', $text);
			$section->addText($text, $textFontStyle, $textFontWithoutSpace);
		}

		if (in_array(self::TASK_POSITIONS, $this->next_additional)) {
			$text = implode(', ',
				array_map(function($task) {
					return $task->getSitePositions();
				}, $this->next_tasks)
			);
			$text = sprintf('Ссылка на гостевой доступ системы отслеживания позиций: %s', $text);
			$section->addText($text, $textFontStyle, $textFontWithoutSpace);
		}

		// Открытые вопросы по проекту
		if (count($this->question_tasks) > 0) {
			$section->addTextBreak();
			$section->addText('Открытые вопросы по проекту:', $secondaryFontStyle);
			$section->addTextBreak();
			$data = $this->getTableData($this->question_tasks, $this->question_columns);
			$this->addTable($section, $data);
		}
		
		// Заключение
		$section->addTextBreak();
		$section->addText('Заключение:', $secondaryFontStyle);
		$text = implode('. ',
			array_map(function($task) {
				return $this->decodeEntities($task->getConclusion());
			}, $this->current_tasks)
		);
		$section->addText($text, $textFontStyle);

		$this->phpword = $phpword;
	}

	/**
	 * Преобразование HTML сущностей
	 * 
	 * @param string $value 
	 * @return string
	 */
	private function decodeEntities($value)
	{
		$value = html_entity_decode($value);
		$value = br2nl($value);
		$value = preg_replace('/(\S)(https?\:\/\/)/i', "\$1\r\n\$2", $value);
		$value = strip_tags($value);

		return $value;
	}

	/**
	 * Значение столбца для заданной задачи
	 * 
	 * @param \App\Planfix\Entity\Task $task 
	 * @param int $column 
	 * @return string
	 */
	private function getTableValue(Task $task, $column)
	{
		switch ($column) {
			case self::TASK_TITLE:
				return $this->decodeEntities($task->title);
				break;
			case self::TASK_COMMENT:
				return $this->decodeEntities($task->getResultOfProcessing());
				break;
			case self::TASK_STORY:
				return $task->getStoryPoints();
				break;
			case self::TASK_STATUS:
				return $task->taskStatus ? $task->taskStatus->name : '';
				break;
		}

		return '';
	}

	/**
	 * Заголовки указанных столбцов
	 * 
	 * @param array $columns 
	 * @return array
	 */
	private function getTableColumns(array $columns)
	{
		return array_intersect_key($this->columns, array_fill_keys($columns, true));
	}

	/**
	 * Данные таблицы
	 * 
	 * @param array $tasks 
	 * @param array $columns 
	 * @return array
	 */
	private function getTableData(array $tasks, array $columns)
	{
		$taskColumns = $this->getTableColumns($columns);
		
		foreach ($taskColumns as $column => $title) {
			if (count($taskColumns) > 1 && isset($taskColumns[self::TASK_COMMENT])) {
				$width = $column == self::TASK_COMMENT ? 50 : (50 / (count($taskColumns) - 1));
			} else {
				$width = 100 / count($taskColumns);
			}
			$taskColumns[$column] = [
				'width' => $width,
				'value' => $title,
			];
		}

		$data = [array_values($taskColumns)];

		$tasks = $this->terminal(
			$this->flatten($tasks)
		);
		
		foreach ($tasks as $task) {
			$data[] = array_map(function($column) use ($task, $taskColumns) {
				return [
					'width' => $taskColumns[$column]['width'],
					'value' => $this->getTableValue($task, $column),
				];
			}, array_keys($taskColumns));
		}

		if (isset($taskColumns[self::TASK_STORY])) {
			$storyCellStyle = [
				'fontStyle' => ['bold' => true],
			];
			$storyTotal = array_reduce($tasks, function($memo, $task) {
				return $memo += $this->getTableValue($task, self::TASK_STORY);
			}, 0);
			$row = [];
			foreach (array_keys($taskColumns) as $index => $key) {
				if ($key == self::TASK_STORY) {
					if ($index > 0) {
						$row[0] = array_merge($storyCellStyle, [
							'value' => 'Итого',
						]);
						$row[$index] = array_merge($storyCellStyle, [
							'value' => $storyTotal,
						]);
					} else {
						$row[$index] = array_merge($storyCellStyle, [
							'value' => "Итого {$storyTotal}",
						]);
					}
				} else {
					$row[$index] = '';
				}
			}
			$data[] = $row;
		}

		return $data;
	}

	/**
	 * Добавить таблицу
	 * 
	 * @param \PhpOffice\PhpWord\Element\Section $section
	 * @param array $data 
	 */
	private function addTable($section, array $data)
	{
		$tableStyleDefault = [
			'borderSize' => 1,
			'cellMargin' => 100,
			'afterSpacing' => 0,
			'cellSpacing ' => 100,
			'spacing' => 0,
			'width' => 100,
			'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
		];

		$cellStyleDefault = [
			'valign' => 'center',
		];

		$cellFontStyleDefault = [
			'name' => 'Arial', 
			'size' => 11
		];

		$cellParagraphStyleDefault = [
			'align' => 'left', 
			'spaceAfter' => 0,
		];

		$table = $section->addTable($tableStyleDefault);
		foreach ($data as $row => $cells) {
			$row = $table->addRow();
			foreach ($cells as $val) {

				if (is_array($val)) {
					$val = (object)$val;
				}

				$cellWidth = 100 / count($cells);
				
				$cellStyle = $cellStyleDefault;
				$cellFontStyle = $cellFontStyleDefault;
				$cellParagraphStyle = $cellParagraphStyleDefault;
				$cellSpan = false;

				if (is_object($val)) {
					$cellWidth = isset($val->width) ? $val->width : $cellWidth;
					$cellValue = isset($val->value) ? $val->value : '';
					$cellSpan = isset($val->span) ? $val->span : $cellSpan;
					if (isset($val->style)) {
						$cellStyle = array_merge($cellStyle, $val->style);
					}
					if (isset($val->fontStyle)) {
						$cellFontStyle = array_merge($cellFontStyle, $val->fontStyle);
					}
					if (isset($val->paragraphStyle)) {
						$cellParagraphStyle = array_merge($cellParagraphStyle, $val->paragraphStyle);
					}
				} else {
					$cellValue = (string)$val;
				}

				$cellWidth = 100 * $cellWidth;
				$cellWidth = (int)$cellWidth;

				$cell = $row->addCell($cellWidth, $cellStyle);
				$cell->addText($cellValue, $cellFontStyle, $cellParagraphStyle);

				if ($cellSpan) {
					$cell->getStyle()->setGridSpan($cellSpan);
				}
			}
		}
	}

	/**
	 * Сохранить отчет
	 * 
	 * @param string $path 
	 */
	public function saveAs($path)
	{
		if (! $this->phpword) {
			$this->generate();
		}

		$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($this->phpword, 'Word2007');
		$objWriter->save($path);
	}
}