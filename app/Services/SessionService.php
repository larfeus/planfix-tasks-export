<?php

namespace App\Services;

use App\Contracts\Service;

class SessionService extends Service
{
	/**
	 * Регистрация или возобновление сессии
	 */
	public function start()
	{
		session_start();
	}
	
	/**
	 * Записать параметр в сессию
	 * 
	 * @param string $name 
	 * @param mixed $value 
	 */
	public function put($name, $value)
	{
		$_SESSION[$name] = is_callable($value) ? call_user_func($value) : $value;
	}

	/**
	 * Записать список параметров в сессию
	 * 
	 * @param array $list
	 */
	public function puts(...$list)
	{
		foreach ($list as $key => $value) {
			$this->put($key, $value);
		}
	}

	/**
	 * Зеркальный метод для $this->put
	 */
	public function set(...$arguments)
	{
		return $this->put(...$arguments);
	}
	
	/**
	 * Получить сохраненный в сессии параметр
	 * 
	 * @param string $name 
	 * @param mixed $default 
	 * @param boolean $store 
	 * @return mixed
	 */
	public function get($name, $default = null, $store = false)
	{
		if ($this->exists($name)) {
			return $_SESSION[$name];
		}

		$value = is_callable($default) ? call_user_func($default) : $default;

		if ($store) {
			$this->set($name, $value);
		}

		return $value;
	}
	
	/**
	 * Получить параметр и удалить значение из сессии
	 * 
	 * @param string $name 
	 * @return mixed
	 */
	public function pull($name)
	{
		$value = $this->get($name);

		$this->forget($name);

		return $value;
	}
	
	/**
	 * Удалить параметры из сессии
	 * 
	 * @param array $list
	 */
	public function forget(...$list)
	{
		if (count($list) > 0) {
			if (is_array($list[0])) {
				$list = $list[0];
			}
		}

		foreach ($list as $key) {
			unset($_SESSION[$key]);
		}
	}
	
	/**
	 * Очистить сессию
	 */
	public function flush()
	{
		$this->forget(array_keys($_SESSION));
	}

	/**
	 * Параметр в сессии существует и отличен от null
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public function has($name)
	{
		return isset($_SESSION[$name]);
	}

	/**
	 * Параметр в сессии существует с любым значением
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public function exists($name)
	{
		return array_key_exists($name, $_SESSION);
	}

	/**
	 * Все значения сессии
	 * 
	 * @return array
	 */
	public function all()
	{
		return $_SESSION;
	}
}