<?php

namespace App\Services;

use App\Contracts\Service;
use App\Exceptions\GoogleDriveAuthorizationException;
use Interop\Container\ContainerInterface;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

class GoogleDriveService extends Service
{
	/**
	 * @var \Google_Client
	 */
	protected $client;

	/**
	 * @var \Google_Service_Drive
	 */
	protected $drive;

	/**
	 * Конструктор
	 * 
	 * @param \Interop\Container\ContainerInterface $container 
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;

		$config = require config_path() . '/googledrive.php';

		$client = new Google_Client();
		$client->setClientId($config['client_id']);
		$client->setClientSecret($config['client_secret']);
		$client->setRedirectUri($config['callback']);
		$client->setAccessType($config['access_type']);
		$client->addScope(Google_Service_Drive::DRIVE_FILE);

		$this->client = $client;
	}

	/**
	 * Сохранение токена авторизации, полученного через callback
	 * 
	 * @param string $code 
	 */
	public function authenticate($code)
	{
		$accessToken = $this->client->authenticate($code);

		$this->container->session->put($this->getSessionTokenName(), $accessToken);
	}

	/**
	 * Проверка авторизации
	 */
	public function authorize()
	{
		$client = $this->client;

		$session = $this->container->session;

		// пытаем найти токен авторизации
		do {
			$accessToken = $session->get(
				$this->getSessionTokenName()
			);

			if (! $accessToken) {
				break;
			}

			$client->setAccessToken($accessToken);
			if (! $client->isAccessTokenExpired()) {
				break;
			}

			if ($refreshToken = $client->getRefreshToken()) {
				$accessToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);
			}

		} while(false);

		// перенаправляем на сервис авторизации google
		if (! $accessToken) {
			throw new GoogleDriveAuthorizationException;
		}

		// токен получен с ошибкой
		if (array_key_exists('error', $accessToken)) {
			throw new \Exception(join(', ', $accessToken));
		}

		$session->put(
			$this->getSessionTokenName(),
			$accessToken
		);
	}

	/**
	 * Адрес для перенаправления на сервис авторизации google
	 * 
	 * @return string
	 */
	public function getAuthUrl()
	{
		$this->setState([
			'from' => (string)$this->container->request->getUri()
		]);

		return $this->client->createAuthUrl();
	}

	/**
	 * Сохранить состояние запроса
	 * 
	 * @param mixed $data 
	 */
	public function setState($data)
	{
		$data = json_encode($data);
		
		$this->client->setState(base64urlencode($data));
	}

	/**
	 * Восстановить состояние запроса
	 * 
	 * @return mixed
	 */
	public function getState()
	{
		$data = $this->container->request->getParam('state');

		return json_decode(base64urldecode($data), true);
	}

	/**
	 * Наименование переменной в сессии для хранения токена
	 * 
	 * @return string
	 */
	public function getSessionTokenName()
	{
		return 'google.oauth2.token';
	}

	/**
	 * Объект для работы с файлами и директориями
	 * 
	 * @return \Google_Service_Drive
	 */
	public function getDrive()
	{
		if (! $this->drive) {
			$this->drive = new Google_Service_Drive($this->client);
		}

		return $this->drive;
	}

	/**
	 * Создать директорию
	 * 
	 * @param string $name 
	 * @param \Google_Service_Drive_DriveFile|null $parent 
	 * @return \Google_Service_Drive_DriveFile
	 */
	public function createFolder($name, $parent = null)
	{
		$drive = $this->getDrive();

		return $drive->files->create(
			new Google_Service_Drive_DriveFile([
				'name' => $name,
				'mimeType' => 'application/vnd.google-apps.folder',
				'parents' => $parent instanceof Google_Service_Drive_DriveFile
					? [$parent->id] : null
			]),
			['fields' => 'id']
		);
	}

	/**
	 * Загрузить файл с заданными параметрами
	 * 
	 * @param string $name 
	 * @param string $path 
	 * @param string $mimeType 
	 * @param \Google_Service_Drive_DriveFile|null $parent 
	 * @return \Google_Service_Drive_DriveFile|null
	 */
	public function uploadFile($name, $path, $mimeType, $parent = null)
	{
		$drive = $this->getDrive();

		if (!file_exists($path) || !is_readable($path)) {
			return null;
		}

		if (! ($content = @file_get_contents($path))) {
			return null;
		}

		return $drive->files->create(
			new Google_Service_Drive_DriveFile([
				'name' => $name,
				'parents' => $parent instanceof Google_Service_Drive_DriveFile
					? [$parent->id] : null
			]),
			[
				'data' => $content,
				'mimeType' => $mimeType,
				'uploadType' => 'multipart',
				'fields' => 'id',
			]
		);
	}

	/**
	 * Удалить файл
	 * 
	 * @param \Google_Service_Drive_DriveFile $file 
	 */
	public function deleteFile(Google_Service_Drive_DriveFile $file)
	{
		$this->getDrive()->files->delete(
			$file->id
		);
	}

	/**
	 * Получить список файлов и директорий
	 * 
	 * @param array $options 
	 * @return type
	 */
	public function getFileList(array $options = [])
	{
		$drive = $this->getDrive();

		$settings = [
			'q' => ['trashed != true'],
		  	'fields' => 'nextPageToken, files(id, name, parents, fileExtension, mimeType, size)'
		];

		foreach ($options as $key => $value) {
			if ($value) {
				switch ($key) {
					case 'name':
						$settings['q'][] = 'name contains \'"' . $value . '"\'';
						break;
					case 'parents':
					case 'parent':
						$settings['q'][] = '\'' . $value . '\' in parents';
						break;
					case 'folders':
						$settings['q'][] = 'mimeType ' . ($value ? '=' : '!=') . '\'application/vnd.google-apps.folder\'';
						break;
				}
			}
		}

		$settings['q'] = implode(' and ', $settings['q']);
		
		$files = [];
		do {
			$list = $drive->files->listFiles($settings);
			$files = array_merge($files, $list->getFiles());
			$nextPageToken = $list->getNextPageToken();
			$settings['pageToken'] = $nextPageToken;
		} while($nextPageToken != null);

		return $files;
	}
}