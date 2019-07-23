<?php

namespace App\Controllers;

use Slim\Http\Response;

class PerformanceController extends Controller
{
	/**
	 * Страница
	 * 
	 * @return \Slim\Http\Response
	 */
	public function index()
	{
		return $this->render('performance/index.twig');
	}

	/**
	 * Очистить кэш сессии
	 * 
	 * @return \Slim\Http\Response
	 */
	public function flush()
	{
		$this->getService('session')->flush();
		
		return $this->route('home');
	}
}