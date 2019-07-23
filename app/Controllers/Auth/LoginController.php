<?php

namespace App\Controllers\Auth;

use App\User;
use App\Controllers\Controller;
use Slim\Http\Response;

class LoginController extends Controller
{
	/**
	 * Страница логина
	 * 
	 * @return \Slim\Http\Response
	 */
	public function index()
	{
		return $this->render('auth/login.twig');
	}

	/**
	 * Авторизация
	 * 
	 * @return \Slim\Http\Response
	 */
	public function login()
	{
		$request = $this->getRequest();

		$username = $request->getParam('username');
		$password = $request->getParam('password');

		try {
			
			$sid = $this->getService('planfix')->login(
				$username, $password
			);

			$this->getService('auth')->login(
				new User(
					compact('username', 'password', 'sid')
				)
			);

			return $this->route('home');

		} catch (\Exception $e) {
			$this->getService('flash')->addMessage('error', $e->getMessage());
		}

		return $this->back();
	}
}