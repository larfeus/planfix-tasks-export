<?php

namespace App\Services;

use App\User;
use App\Contracts\Service;
use Interop\Container\ContainerInterface;

class AuthService extends Service
{
	/**
	 * Конструктор
	 * 
	 * @param \Interop\Container\ContainerInterface $container 
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;		
	}

	/**
	 * Наименование атрибута для сохранения авторизации в сессии
	 * 
	 * @return string
	 */
	public function getSessionAttribute()
	{
		return 'user';
	}

	/**
	 * Гость
	 * 
	 * @return boolean
	 */
	public function guest()
	{
		return ! $this->getService('session')->has(
			$this->getSessionAttribute()
		);
	}

	/**
	 * Войти
	 * 
	 * @param \App\User $user 
	 */
	public function login(User $user)
	{
		$this->getService('session')->put(
			$this->getSessionAttribute(),
			$user
		);
	}

	/**
	 * Разлогиниться
	 */
	public function logout()
	{
		$this->getService('session')->forget(
			$this->getSessionAttribute()
		);
	}

	/**
	 * Авторизованный пользователь
	 * 
	 * @return \App\User
	 */
	public function getUser()
	{
		return $this->getService('session')->get(
			$this->getSessionAttribute()
		);
	}
}