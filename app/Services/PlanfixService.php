<?php

namespace App\Services;

use App\User;
use App\Planfix\Api;
use App\Planfix\Entity\Project;
use App\Planfix\Entity\Task;
use App\Planfix\Entity\TaskStatus;
use App\Planfix\Pagination\Paginator;
use App\Planfix\Mapping\DataMapper;
use App\Planfix\Mapping\CollectionMapper;
use App\Contracts\Service;
use Interop\Container\ContainerInterface;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\AuthorizationException;

class PlanfixService extends Service
{
	/**
	 * @var \App\Planfix\Api
	 */
	protected $api;

	/**
	 * Конструктор
	 * 
	 * @param \Interop\Container\ContainerInterface $container 
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;	

		$config = require config_path() . '/planfix.php';

		$this->api = (new Api(
			$config['key'],
			$config['secret']
		))->setAccount($config['account']);

		if (! $container->auth->guest()) {

			$user = $container->auth->getUser();

			$this->api->setUser(
				$user->username, $user->password
			);

			$this->api->setSid($user->sid);
		}
	}

	/**
	 * Авторизация
	 * 
	 * @param string $username 
	 * @param string $password 
	 * @return type
	 */
	public function login($username, $password)
	{
		$this->log('Planfix auth request with username:%s', $username);

		try {
			$this->api->setUser(
				$username, $password
			)->auth();
		} catch(\Exception $e) {
			throw new AuthorizationException($e->getMessage());
		}

		$this->log('Planfix successfull authorization');

		return $this->api->getSid();
	}

	/**
	 * Выполнение запроса к api
	 * 
	 * @param array ...$arguments 
	 * @return mixed
	 */
	public function request(...$arguments)
	{
		$this->log('Planfix request to %s', $arguments[0]);

		$response = $this->api->call(...$arguments);

		if ($response['error_code'] == '0005') {
			throw new UnauthorizedException;
		}

		return $response;
	}

	/**
	 * Логирование
	 * 
	 * @param string $message 
	 */
	public function log($message)
	{
		$args = func_get_args();

		$this->container->logger->debug(
			vsprintf($message, array_slice($args, 1) ? : [])
		);
	}

	/**
	 * Примитивная реализация кеширования вызовов
	 * 
	 * @param string $name 
	 * @param mixed $default 
	 * @param integer $seconds 
	 * @return mixed
	 */
	public function cache($name, $default, $seconds = null)
	{
		$session = $this->container->session;

		do {
			if (! is_array($cached = $session->get($name))) {
				break;
			}

			list($value, $timestamp) = $cached;

			if ($seconds && $timestamp + $seconds <= time()) {
				break;
			}

			if ($value instanceof Paginator) {
				if ($value->count() == 0 && $value->getCurrentPage() > 0) {
					break;
				}
			}

			return $value;

		} while(false);

		$value = is_callable($default, true) ? call_user_func($default) : $default;

		if ($value) {
			$session->set($name, [$value, time()]);
		}

		return $value;
	}

	/**
	 * Список проектов
	 * 
	 * @return \App\Planfix\Pagination\Paginator
	 */
	public function getProjectList()
	{
		return $this->cache('planfix.projectList', new Paginator(), 3600)
			->setCallback(function($page) {
				return (new CollectionMapper(
					function() use ($page) {
						return $this->request('project.getList', [
							'target' => 'all',
							'pageCurrent' => $page
						]);
					}, 'data.projects.project')
				)->transform(Project::class);
			});
	}

	/**
	 * Проект
	 * 
	 * @param integer $id
	 * @return \App\Planfix\Entity\Project
	 */
	public function getProject($id)
	{
		return $this->cache("planfix.project.{$id}", function() use ($id) {
			return (new DataMapper(
				function() use ($id) {
					return $this->request('project.get', [
						'project' => [
							['id' => $id]
						]
					]);
				}, 'data.project')
			)->transform(Project::class);
		}, 60);
	}

	/**
	 * Список задач
	 * 
	 * @return \App\Planfix\Pagination\Paginator
	 */
	public function getProjectTaskList($project_id)
	{
		return $this->cache("planfix.projectTaskList.{$project_id}", new Paginator(), 3600)
			->setCallback(function($page) use ($project_id) {
				return (new CollectionMapper(
					function() use ($page, $project_id) {
						return $this->request('task.getList', [
							'project' => [
								['id' => $project_id]
							],
							'pageCurrent' => $page,
							'target' => 'all'
						]);
					}, 'data.tasks.task')
				)->transform(Task::class);
			});
	}

	/**
	 * Задача
	 * 
	 * @param integer $id
	 * @return \App\Planfix\Entity\Task
	 */
	public function getTask($id)
	{
		return $this->cache("planfix.task.{$id}", function() use ($id) {
			return (new DataMapper(
				function() use ($id) {
					return $this->request('task.get', [
						'task' => [
							['id' => $id]
						]
					]);
				}, 'data.task')
			)->transform(Task::class);
		}, 60);
	}

	/**
	 * Список статусов для заданного сета
	 * 
	 * @param integer $statusSetId 
	 * @return \App\Planfix\Entity\Collection
	 */
	public function getTaskStatusList($statusSetId)
	{
		return $this->cache("planfix.statusList.{$statusSetId}", function() use ($statusSetId) {
			return (new CollectionMapper(
				function() use ($statusSetId) {
					return $this->request('taskStatus.getListOfSet', [
						'taskStatusSet' => [
							['id' => $statusSetId]
						]
					]);
				}, 'data.taskStatuses.taskStatus')
			)->transform(TaskStatus::class);
		}, 3600);
	}

	/**
	 * Статус задачи
	 * 
	 * @param \App\Planfix\Entity\Task $task 
	 * @return \App\Planfix\Entity\TaskStatus|null
	 */
	public function getTaskStatus(Task $task)
	{
		$statusList = $this->getTaskStatusList($task->statusSet);

		foreach ($statusList->items as $taskStatus) {
			if ($taskStatus->id == $task->status) {
				return $taskStatus;
			}
		}

		return null;
	}
}