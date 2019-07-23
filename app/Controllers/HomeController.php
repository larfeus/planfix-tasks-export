<?php

namespace App\Controllers;

use App\Report;
use App\ReportFile;
use Slim\Http\Response;

class HomeController extends Controller
{
	/**
	 * Формирование отчета и сохранение его на сервере
	 * 
	 * @param \App\Report $report
	 * @return \Slim\Http\Response
	 */
	public function generate(Report $report)
	{
		$reportFile = ReportFile::create($report);
		$reportFilePath = $reportFile->getPath();
		$reportFileFolder = dirname($reportFilePath);

		if (! file_exists($reportFileFolder)) {
			mkdir($reportFileFolder, 0777, true);
		}

		if (file_exists($reportFilePath)) {
			unlink($reportFilePath);
		}

		$report->generate();
		$report->saveAs($reportFilePath);

		$flash = $this->getService('flash');
		$flash->addMessage('message', 'Отчет успешно сгенерирован');

		return $this->route('report', [
			'year' => $reportFile->year,
			'month' => $reportFile->month,
			'project_id' => $reportFile->project_id,
			'name' => urlencode($reportFile->name),
		]);
	}

	/**
	 * Страница с выбором основных параметров
	 * 
	 * @return \Slim\Http\Response
	 */
	public function index()
	{
		$request = $this->getRequest();
		$planfix = $this->getService('planfix');

		// отчет
		$report = new Report();
		$report_columns = $report->columns;
		$report_additional = $report->additional;

		// выбор проекта
		$project = ($project_id = $request->getParam('project_id'))
			? $planfix->getProject($project_id) : null;

		// Выбор отчетного года и месяца
		$year = $request->getParam('year', date('Y'));
		$month = $request->getParam('month', date('n'));

		// список задач для выбора
		$task_list = $project ? $planfix->getProjectTaskList($project_id) : [];

		// преобразовываем список задач в древовидную структуру
		$task_tree = $task_list ? $this->createTaskTree($task_list) : [];

		// выбор задач
		foreach (['current', 'next', 'question'] as $attribute) {
			$selected = (array)$request->getParam("{$attribute}_task_id", []);
			${"{$attribute}_tasks"} = array_filter($task_list ? $task_list->all() : [], function($task) use ($selected) {
				return in_array($task->id, $selected);
			});
			$variable = "{$attribute}_columns";
			$$variable =  ($$variable = $request->getParam($variable))
				? (array)$$variable : $report->{$variable};
		}

		$next_task = array_shift($next_tasks);

		// выбор дополнительной информации
		$next_additional = (array)$request->getParam('next_additional') ? : $report->next_additional;
		
		// прочие списки
		$project_list = $planfix->getProjectList();
		$year_list = $this->getYearList();
		$month_list = $this->getMonthList();

		// Запуск процесса генерации отчета
		if ($request->getParam('generate')) {

			$report->project = $project;
			$report->month = $month;
			$report->year = $year;
			$report->current_tasks = $current_tasks;
			$report->current_columns = $current_columns;
			$report->next_tasks = [$next_task];
			$report->next_columns = $next_columns;
			$report->next_additional = $next_additional;
			$report->question_tasks = $question_tasks;
			$report->question_columns = $question_columns;

			return $this->generate($report);
		}

		return $this->render('home/index.twig',
			compact([
				'project',
				'project_list',
				'year',
				'year_list',
				'month',
				'month_list',
				'current_tasks',
				'current_columns',
				'next_task',
				'next_columns',
				'next_additional',
				'question_tasks',
				'question_columns',
				'task_list',
				'task_tree',
				'report_columns',
				'report_additional',
			])
		);
	}

	/**
	 * Список годов
	 * 
	 * @return array
	 */
	protected function getYearList()
	{
		return range(date('Y') - 5, date('Y'));
	}

	/**
	 * Список месяцев
	 * 
	 * @return array
	 */
	protected function getMonthList()
	{
		return get_months();
	}

	/**
	 * Создание древовидной структуры списка задач
	 * 
	 * @param \Traversable $collection 
	 * @return array
	 */
	protected function createTaskTree($collection)
	{
		$planfix = $this->getService('planfix');

		$tasks = [];
		foreach ($collection as $task) {
			$task->tasks = [];
			$task->hasParent = false;
			$task->taskStatus = $planfix->getTaskStatus($task);
			$tasks[$task->id] = $task;
		}

		foreach ($tasks as $task) {
			if (isset($tasks[$task->parent->id])) {
				$this->addChilsTask($tasks[$task->parent->id], $task);
			}
		}

		foreach ($tasks as $id => $task) {
			if ($task->hasParent) {
				unset($tasks[$id]);
			}
		}

		return $tasks;
	}

	/**
	 * Добавление задачи в зависимые
	 * 
	 * @param \App\Planfix\Entity\Task $parent 
	 * @param \App\Planfix\Entity\Task $child 
	 */
	protected function addChilsTask($parent, $child)
	{
		if (! isset($parent->tasks)) {
			$parent->tasks = [];
		}

		$parent->tasks[] = $child;
		$child->hasParent = true;
	}
}