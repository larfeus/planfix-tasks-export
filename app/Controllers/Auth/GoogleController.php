<?php

namespace App\Controllers\Auth;

use App\Controllers\Controller;
use Slim\Http\Response;

class GoogleController extends Controller
{
	/**
	 * Точка обратного вызова OAuth2 авторизации google
	 * 
	 * @return \Slim\Http\Response
	 */
	public function callback()
	{
		$googledrive = $this->getService('googledrive');
		$googledrive->authenticate(
			$this->getRequest()->getParam('code')
		);

		$state = $googledrive->getState();

		return $this->redirect(
			$state['from']
		);
	}
}