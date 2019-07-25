<?php

namespace App\Controllers;

use App\ReportFile;
use Slim\Http\Response;
use Slim\Http\Stream;
use Slim\Exception\NotFoundException;

class ReportController extends Controller
{
	/**
	 * Поиск файла отчета по заданным параметрам
	 * 
	 * @param int $year
	 * @param int $month
	 * @param int $project_id
	 * @param string $name 
	 * @return \App\ReportFile
	 */
	protected function getReportFile($year, $month, $project_id, $name)
	{
		$attributes = compact([
			'year', 
			'month', 
			'project_id',  
			'name',  
		]);

		$file = ReportFile::find(
			$attributes
		);

		if (! $file) {
			throw new NotFoundException;
		}

		return $file;
	}

	/**
	 * Информация по отчету
	 * 
	 * @param int $year
	 * @param int $month
	 * @param int $project_id
	 * @param string $name 
	 * @return \Slim\Http\Response
	 */
	public function show($year, $month, $project_id, $name)
	{
		$router = $this->getService('router');
		$arguments = compact([
			'year',
			'month',
			'project_id',
			'name',
		]);

		$reportFile = $this->getReportFile($year, $month, $project_id, urldecode($name));

		$reportDownloadUrl = $router->pathFor('download', $arguments);
		$reportUploadUrl = $router->pathFor('upload', $arguments);

		return $this->render('report/show.twig',
			compact([
				'reportDownloadUrl',
				'reportUploadUrl',
				'reportFile',
			])
		);
	}

	/**
	 * Скачать отчет
	 * 
	 * @param int $year
	 * @param int $month
	 * @param int $project_id
	 * @param string $name 
	 * @return \Slim\Http\Response
	 */
	public function download($year, $month, $project_id, $name)
	{
		$reportFile = $this->getReportFile($year, $month, $project_id, urldecode($name));

		try {
			$path = $reportFile->getPath();

			$resource = fopen($path, 'rb');
			$stream = new Stream($resource);

			$response = $this->getResponse()
				->withHeader('Content-Type', 'application/octet-stream')
				->withHeader('Content-Description', 'File Transfer')
				->withHeader('Content-Disposition', 'attachment; filename="' . $reportFile->name . '"')
				->withHeader('Content-Length', $reportFile->getSize())
				->withHeader('Expires', '0')
				->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
				->withHeader('Pragma', 'public');

			$reportFile->read();

			return $response;

		} catch (\Exception $e) {
			$this->getService('logger')->error('Error while sending file', ['exception' => $e]);
		} finally {
			if (isset($stream)) {
				$stream->close();
			}
		}
	}

	/**
	 * Загрузить отчет в GoogleDrive
	 * 
	 * @param int $year
	 * @param int $month
	 * @param int $project_id
	 * @param string $name 
	 * @return \Slim\Http\Response
	 */
	public function upload($year, $month, $project_id, $name)
	{
		$reportFile = $this->getReportFile($year, $month, $project_id, urldecode($name));

		if (! $reportFile) {
			return $this->route('home');
		}

		$googledrive = $this->getService('googledrive');
		$googledrive->authorize();

		$auth = $this->getService('auth');
		$user = $auth->getUser();

		$planfix = $this->getService('planfix');
		$project = $planfix->getProject($project_id);

		$path = [
			sprintf('%s_PLANFIX_ОТЧЕТЫ', strtoupper($user->username)),
			sprintf('ПРОЕКТ_%s', $project ? $project->title : $reportFile->project_id),
			$reportFile->name
		];

		$folder = null;
		foreach ($path as $index => $pathChunkName) {
			if ($index == count($path) - 1) {

				$files = $googledrive->getFileList([
					'name' => $pathChunkName,
					'folders' => false,
					'parents' => $folder ? $folder->id : null
				]);

				if (count($files) > 0) {
					$googledrive->deleteFile(
						array_shift($files)
					);
				}

				$uploaded = $googledrive->uploadFile(
					$pathChunkName,
					$reportFile->getPath(),
					$reportFile->getMimeType(),
					$folder
				);
			} else {
				$folders = $googledrive->getFileList([
					'name' => $pathChunkName,
					'folders' => true,
					'parents' => $folder ? $folder->id : null
				]);
				if (count($folders) > 0) {
					$folder = array_shift($folders);
				} else {
					$folder = $googledrive->createFolder($pathChunkName, $folder);
				}
			}
		}

		if (! isset($uploaded)) {
			$uploaded = false;
		}

		$flash = $this->getService('flash');
		if ($uploaded) {
			$flash->addMessage('message', 'Отчет успешно загружен на Ваш GoogleDrive.');
			$flash->addMessage('uploaded', $uploaded->webViewLink);
		} else {
			$flash->addMessage('error', 'Не удалось загрузить отчет на Ваш GoogleDrive.');
		}

		return $this->route('report', 
			compact([
				'year',
				'month',
				'project_id',
				'name',
			])
		);
	}
}