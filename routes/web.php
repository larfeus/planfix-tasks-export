<?php

/*
 * Авторизация
 */

$app->get('/login', 'App\Controllers\Auth\LoginController:index')
	->setName('login');

$app->post('/login', 'App\Controllers\Auth\LoginController:login');
$app->get('/logout', 'App\Controllers\Auth\LogoutController:index')
	->setName('logout');

/*
 * Защищенные роуты
 */

$app->group('/', function() use ($app) {

	// авторизация в гуглдокс
	$app->get('google/callback', 'App\Controllers\Auth\GoogleController:callback')
		->setName('google_callback');

	// работа с готовыми отчетами
	$app->get('report/{year}/{month}/{project_id}/{name}', 'App\Controllers\ReportController:show')
		->setName('report');

	$app->get('download/{year}/{month}/{project_id}/{name}', 'App\Controllers\ReportController:download')
		->setName('download');

	$app->get('upload/{year}/{month}/{project_id}/{name}', 'App\Controllers\ReportController:upload')
		->setName('upload');

	// производительность
	$app->get('performance', 'App\Controllers\PerformanceController:index')
		->setName('performance');

	$app->post('performance', 'App\Controllers\PerformanceController:flush');

	// пошаговая форма генерации отчета
	$app->map(['GET', 'POST'], '', 'App\Controllers\HomeController:index')
		->setName('home');
		
})->add(
	\App\Middlewares\AuthMiddleware::class
);