<?php

namespace App\Controllers\Auth;

use App\Controllers\Controller;
use Slim\Http\Response;

class LogoutController extends Controller
{
	/**
	 * Выход
	 * 
	 * @return \Slim\Http\Response
	 */
	public function index()
	{
		$this->getService('auth')->logout();

		return $this->route('home');
	}
}