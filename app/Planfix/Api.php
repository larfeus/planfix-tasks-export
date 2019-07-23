<?php

namespace App\Planfix;

class Api
{
    const MAX_PAGE_SIZE = 100;
    const MAX_BATCH_SIZE = 10;

    /**
     * Default Curl options
     * @var array
     */
    public static $curlParams = [
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0
    ];

    /**
     * API-key
     *
     * @var string
     */
    protected $apiKey;

    /**
     * API-privateKey
     *
     * @var string
     */
    protected $privateKey;

    /**
     * User login
     *
     * @var string
     */
    protected $userLogin;

    /**
     * User password
     *
     * @var string
     */
    protected $userPassword;

    /**
     * API root URL
     * @var string
     */
    public $root = 'https://api.planfix.ru/xml/';

    /**
     * Account name (*.planfix.ru)
     * @var string
     */
    protected $account;

    /**
     * Session id
     * @var string
     */
    protected $sid;

    /**
     * [
     *      code => text
     * ]
     * @var array
     */
    public static $errorMap = [
        '0001' => 'Неверный API Key',
        '0002' => 'Приложение заблокировано',
        '0003' => 'Ошибка XML разбора. Некорректный XML',
        '0004' => 'Неизвестный аккаунт',
        '0005' => 'Ключ сессии недействителен (время жизни сессии истекло)',
        '0006' => 'Неверная подпись',
        '0007' => 'Превышен лимит использования ресурсов (ограничения, связанные с лицензиями или с количеством запросов)',
        '0008' => 'Неизвестное имя функции',
        '0009' => 'Отсутствует один из обязательных параметров функции',
        '0010' => 'Аккаунт заморожен',
        '0011' => 'На площадке аккаунта производится обновление программного обеспечения',
        '0012' => 'Отсутствует сессия, не передан параметр сессии в запрос',
        '0013' => 'Неопределенный пользователь',
        '0014' => 'Пользователь неактивен',
        '0015' => 'Недопустимое значение параметра',
        '0016' => 'В данном контексте параметр не может принимать переданное значение',
        '0017' => 'Отсутствует значение для зависящего параметра',
        '0018' => 'Функции/функционал не реализована',
        '0019' => 'Заданы конфликтующие между собой параметры',
        '0020' => 'Вызов функции запрещен',
        '0021' => 'Запрошенное количество объектов больше максимально разрешенного для данной функции',
        '0022' => 'Использование API недоступно для бесплатного аккаунта',
        '1001' => 'Неверный логин или пароль',
        '1002' => 'На выполнение данного запроса отсутствуют права (привилегии)',
        '2001' => 'Запрошенный проект не существует',
        '2002' => 'На выполнение данного запроса отсутствуют права (привилегии)',
        '2003' => 'Ошибка добавления проекта',
        '3001' => 'Указанная задача не существует',
        '3002' => 'Нет доступа к над задаче',
        '3003' => 'Проект, в рамках которого создается задача, не существует',
        '3004' => 'Проект, в рамках которого создается задача, не доступен',
        '3005' => 'Ошибка добавления задачи',
        '3006' => 'Время "Приступить к работе" не может быть больше времени "Закончить работу до"',
        '3007' => 'Неопределенная периодичность, скорее всего задано несколько узлов, которые конфликтуют друг с другом или не указан ни один',
        '3008' => 'Нет доступа к задаче',
        '3009' => 'Нет доступа на изменение данных задачи',
        '3010' => 'Данную задачу отклонить нельзя (скорее всего, она уже принята этим пользователем)',
        '3011' => 'Данную задачу принять нельзя (скорее всего, она уже принята этим пользователем)',
        '3012' => 'Пользователь, выполняющий запрос, не является исполнителем задачи',
        '3013' => 'Задача не принята (для выполнения данной функции задача должна быть принята)',
        '4001' => 'На выполнение данного запроса отсутствуют права (привилегии)',
        '4002' => 'Действие не существует',
        '4003' => 'Ошибка добавления действия',
        '4004' => 'Ошибка обновления данных',
        '4005' => 'Ошибка обновления данных',
        '4006' => 'Попытка изменить статус на недозволенный',
        '4007' => 'В данном действии запрещено менять статус',
        '4008' => 'Доступ к комментария/действию отсутствует',
        '4009' => 'Доступ к задаче отсутствует',
        '4010' => 'Указанная аналитика не существует',
        '4011' => 'Для аналитики были переданы не все поля',
        '4012' => 'Указан несуществующий параметр для аналитики',
        '4013' => 'Переданные данные не соответствуют типу поля',
        '4014' => 'Указанный ключ справочника нельзя использовать',
        '4015' => 'Указанный ключ справочника не существует',
        '4016' => 'Указанный ключ данных поля не принадлежит указанной аналитике',
        '5001' => 'Указанная группа пользователей не существует',
        '5002' => 'На выполнение данного запроса отсутствуют права (привилегии)',
        '5003' => 'Ошибка добавления',
        '6001' => 'На выполнение данного запроса отсутствуют права (привилегии)',
        '6002' => 'Данный e-mail уже используется',
        '6003' => 'Ошибка добавления сотрудника',
        '6004' => 'Пользователь не существует',
        '6005' => 'Ошибка обновления данных',
        '6006' => 'Указан идентификатор несуществующей группы пользователей',
        '7001' => 'На выполнение данного запроса отсутствуют права (привилегии)',
        '7002' => 'Клиент не существует',
        '7003' => 'Ошибка добавления клиента',
        '7004' => 'Ошибка обновления данных',
        '8001' => 'На выполнение данного запроса отсутствуют права (привилегии)',
        '8002' => 'Контакт не существует',
        '8003' => 'Ошибка добавления контакта',
        '8004' => 'Ошибка обновления данных',
        '8005' => 'Контакт не активировал доступ в ПланФикс',
        '8006' => 'Контакту не предоставлен доступ в ПланФикс',
        '8007' => 'E-mail, указанный для логина, не уникален',
        '8008' => 'Попытка установки пароля для контакта, не активировавшего доступ в ПланФикс',
        '8009' => 'Ошибка обновления данных для входа в систему',
        '9001' => 'На выполнение данного запроса отсутствуют права (привилегии)',
        '9002' => 'Запрашиваемый файл не существует',
        '9003' => 'Ошибка загрузки файла',
        '9004' => 'Попытка загрузить пустой список файлов',
        '9005' => 'Недопустимый символ в имени файла',
        '9006' => 'Имя файла не уникально',
        '9007' => 'Ошибка файловой системы',
        '9008' => 'Ошибка возникает при попытке добавить файл из проекта для проекта',
        '9009' => 'Файл, который пытаются добавить к задаче, является файлом другого проекта',
        '10001' => 'На выполнение данного запроса отсутствуют права (привилегии)',
        '10002' => 'Аналитика не существует',
        '10003' => 'Переданный параметр группы аналитики не существует',
        '10004' => 'Переданный параметр справочника аналитики не существует',
        '11001' => 'Указанной подписки не существует',
    ];

    /**
     * Initializes a Planfix API-Client
     *
     * @param string $apiKey API-key
     * @param string $privateKey Private-key
     */
    public function __construct($apiKey, $privateKey) {
        $this->setApiKey($apiKey);
        $this->setPrivateKey($privateKey);
    }

    /**
     * Set the API-key
     *
     * @param string $apiKey API-key
     *
     * @return self
     */
    public function setApiKey($apiKey) {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get the API-key
     *
     * @return string the API-key
     */
    public function getApiKey() {
        return $this->apiKey;
    }

    /**
     * Set the API-privateKey
     *
     * @param string $privateKey API-privateKey
     *
     * @return self
     */
    public function setPrivateKey($privateKey) {
        $this->privateKey = $privateKey;

        return $this;
    }

    /**
     * Get the API-privateKey
     *
     * @return string the API-privateKey
     */
    public function getPrivateKey() {
        return $this->privateKey;
    }

    /**
     * Set the Account
     *
     * @param string $account Account
     *
     * @return self
     */
    public function setAccount($account) {
        $this->account = $account;

        return $this;
    }

    /**
     * Get the Account
     *
     * @return string the Account
     */
    public function getAccount() {
        return $this->account;
    }

    /**
     * Set User Credentials
     *
     * @param string $login
     * @param string $password
     *
     * @return self
     */
    public function setUser($login, $password) {
        $this
            ->setUserLogin($login)
            ->setUserPassword($password);

        return $this;
    }

    /**
     * Set the User login
     *
     * @param string $userLogin User login
     *
     * @return self
     */
    public function setUserLogin($userLogin) {
        $this->userLogin = $userLogin;

        return $this;
    }

    /**
     * Get the User login
     *
     * @return string the User login
     */
    public function getUserLogin() {
        return $this->userLogin;
    }

    /**
     * Set the User password
     *
     * @param string $userPassword User password
     *
     * @return self
     */
    public function setUserPassword($userPassword) {
        $this->userPassword = $userPassword;

        return $this;
    }

    /**
     * Get the User password
     * Private for no external use
     *
     * @return string the User password
     */
    private function getUserPassword() {
        return $this->userPassword;
    }

    /**
     * Get the sid
     *
     * @return string the Sid
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * Set the Sid
     *
     * @param string $sid Sid
     *
     * @return self
     */
    public function setSid($sid) {
        $this->sid = $sid;

        return $this;
    }

    /**
     * Authenticate with previously set credentials
     *
     * @throws \Exception
     *
     * @return self
     */
    public function auth()
    {
        if (!($this->getUserLogin() && $this->getUserPassword())) {
            throw new \Exception('User credentials are not set');
        }

        $requestXml = $this->createXml();
        $requestXml['method'] = 'auth.login';
        $requestXml->login = $this->getUserLogin();
        $requestXml->password = $this->getUserPassword();
        $requestXml->signature = $this->signXml($requestXml);

        $response = $this->makeRequest($requestXml);

        if (!$response['success']) {
            throw new \Exception($response['error_message']);
        }

        $this->setSid($response['data']['sid']);

        return $this;
    }

    /**
     * Perform API request
     *
     * @param $method string|array API method to be called or group of methods for batch request
     * @param $params array (optional) Parameters for called API method
     *
     * @throws \Exception
     *
     * @return array the API response
     */
    public function call($method, $params = [])
    {
        if (!$method) {
            throw new \Exception('No method specified');
        } elseif (is_array($method)) {
            if (isset($method['method'])) {
                $params = isset($method['params']) ? $method['params'] : '';
                $method = $method['method'];
            } else {
                foreach($method as $request) {
                    if (!isset($request['method'])) {
                        throw new \Exception('No method specified');
                    }
                }
            }
        }

        $sid = $this->getSid();

        if (!$sid) {
            $this->auth();
            $sid = $this->getSid();
        }

        if (is_array($method)) {
            $batch = [];

            foreach($method as $request) {
                $requestXml = $this->createXml();
                $requestXml['method'] = $request['method'];
                $requestXml->sid = $sid;

                $params = isset($request['params']) ? $request['params'] : '';

                if (is_array($params) && $params) {
                    $this->importParams($requestXml, $params);
                }

                if (!isset($requestXml->pageSize)) {
                    $requestXml->pageSize = self::MAX_PAGE_SIZE;
                }

                $requestXml->signature = $this->signXml($requestXml);

                $batch[] = $requestXml;
            }

            return $this->makeBatchRequest($batch);
        } else {
            $requestXml = $this->createXml();

            $requestXml['method'] = $method;
            $requestXml->sid = $sid;

            if (is_array($params) && $params) {
                $this->importParams($requestXml, $params);
            }

            if (!isset($requestXml->pageSize)) {
                $requestXml->pageSize = self::MAX_PAGE_SIZE;
            }

            $requestXml->signature = $this->signXml($requestXml);
            // dd($requestXml->asXML());
            return $this->makeRequest($requestXml);
        }
    }

    /**
     * Create XML request
     *
     * @throws \Exception
     *
     * @return \SimpleXMLElement the XML request
     */
    protected function createXml()
    {
        $requestXml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><request></request>');
        if (!$this->account) {
            throw new \Exception('Account is not set');
        }
        $requestXml->account = $this->account;

        return $requestXml;
    }

    /**
     * Import parameters to XML request
     *
     * @param \SimpleXMLElement $requestXml
     * @param $params array
     * @param $subKey string|null
     *
     * @return \SimpleXMLElement the XML request
     */
    protected function importParams($requestXml, $params, $subKey = null)
    {
        foreach($params as $key => $value ) {
            if (is_numeric($key) && !is_null($subKey)) {
                $key = $subKey;
            }
            if (is_array($value)) {
                if (!(range(0, count($value) - 1) === array_keys($value))) {
                    $subNode = $requestXml->addChild($key);
                    $this->importParams($subNode, $value);
                } else {
                    $this->importParams($requestXml, $value, $key);
                }
            } else {
                $requestXml->addChild("$key", htmlspecialchars("$value"));
            }
        }

        return $requestXml;
    }

    /**
     * Sign XML request
     *
     * @param \SimpleXMLElement $requestXml The XML request
     *
     * @throws \Exception
     *
     * @return string the Signature
     */
    protected function signXml($requestXml)
    {
        return md5($this->normalizeXml($requestXml) . $this->getPrivateKey());
    }

    /**
     * Normalize the XML request
     *
     * @param \SimpleXMLElement $node $node The XML request
     *
     * @return string the Normalized string
     */
    protected function normalizeXml($node)
    {
        $normStr = '';
        $node = (array) $node;
        ksort($node);
        foreach ($node as $child) {
            if (is_array($child)) {
                $normStr .= implode('', array_map([$this,'normalizeXml'], $child));
            } elseif (is_object($child)) {
                $normStr .= $this->normalizeXml($child);
            } else {
                $normStr .= (string) $child;
            }
        }

        return $normStr;
    }

    /**
     * Make the batch request to API
     *
     * @param $batch array The array of XML requests
     *
     * @return array the array of API responses
     */
    protected function makeBatchRequest($batch)
    {
        $mh = curl_multi_init();

        $batchCnt = count($batch);
        $max_size = $batchCnt < self::MAX_BATCH_SIZE ? $batchCnt : self::MAX_BATCH_SIZE;

        $batchResult = [];

        for ($i = 0; $i < $max_size; $i++) {
            $requestXml = array_shift($batch);
            $ch = $this->prepareCurlHandle($requestXml);
            $chKey = (string) $ch;
            $batchResult[$chKey] = [];
            curl_multi_add_handle($mh, $ch);
        }

        do {
            do {
                $mrc = curl_multi_exec($mh, $running);
            } while($mrc == CURLM_CALL_MULTI_PERFORM);

            while ($request = curl_multi_info_read($mh)) {
                $ch = $request['handle'];
                $chKey = (string) $ch;
                $batchResult[$chKey] = $this->parseAPIResponse(curl_multi_getcontent($ch), curl_error($ch));

                if (count($batch)) {
                    $requestXml = array_shift($batch);
                    $ch = $this->prepareCurlHandle($requestXml);
                    $chKey = (string) $ch;
                    $batchResult[$chKey] = array();
                    curl_multi_add_handle($mh, $ch);
                }

                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }

            if ($running) {
                curl_multi_select($mh);
            }

        } while($running && $mrc == CURLM_OK);

        return array_values($batchResult);
    }

    /**
     * Make the request to API
     *
     * @param \SimpleXMLElement $requestXml The XML request
     *
     * @return array the API response
     */
    protected function makeRequest($requestXml) {
        $ch = $this->prepareCurlHandle($requestXml);

        $response = curl_exec($ch);
        $error = curl_error($ch);

        return $this->parseAPIResponse($response, $error);
    }

    /**
     * Prepare the Curl handle
     *
     * @param \SimpleXMLElement $requestXml The XML request
     *
     * @return resource the Curl handle
     */
    protected function prepareCurlHandle($requestXml) {
        $ch = curl_init($this->root);

        curl_setopt_array($ch, self::$curlParams);

        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->apiKey . ':X');

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestXml->asXML());

        return $ch;
    }

    /**
     * Parse the API response
     *
     * @param string $response The API response
     * @param string $error The Curl error if any
     *
     * @return array the Curl handle
     */
    protected function parseAPIResponse($response, $error) {
        $result = [
            'success'       => 1,
            'error_code'    => '',
            'error_message' => '',
            'meta'          => null,
            'data'          => null
        ];

        if ($error) {
            $result['success'] = 0;
            $result['error_message'] = $error;

            return $result;
        }

        try {
            $responseXml = new \SimpleXMLElement($response);
        } catch (\Exception $e) {
            $result['success'] = 0;
            $result['error_message'] = $e->getMessage();

            return $result;
        }

        if ($responseXml['status'] == 'error') {
            $result['success'] = 0;
            $result['error_code'] = (string) $responseXml->code;
            $result['error_message'] = static::$errorMap[$result['error_code']] ?: null;

            return $result;
        }

        if (isset($responseXml->sid)) {
            $result['data']['sid'] = (string) $responseXml->sid;
        } else {
            $responseXml = $responseXml->children();

            foreach($responseXml->attributes() as $key => $val) {
                $result['meta'][$key] = (int) $val;
            }

            if ($result['meta'] == null || $result['meta']['totalCount'] || $result['meta']['count']) {
                $result['data'] = $this->exportData($responseXml);
            }
        }

        return $result;
    }

    /**
     * Exports the xml response to array
     *
     * @param \SimpleXMLElement $responseXml The API response
     *
     * @return array the Exported data
     */
    protected function exportData($responseXml)
    {
        $items = [];

        if(!is_object($responseXml)) {
            return $items;
        }

        $child = (array) $responseXml;

        if (sizeof($child) > 1) {
            foreach($child as $key => $value) {
                if ($key == '@attributes') {
                    continue;
                }
                if (is_array($value)) {
                    foreach($value as $subKey => $subValue) {
                        if (!is_object($subValue)) {
                            $items[$key][$subKey] = $subValue;
                        } else {
                            if ($subValue instanceof \SimpleXMLElement) {
                                $items[$key][$subKey] = $this->exportData($subValue);
                            }
                        }
                    }
                } else {
                    if (!is_object($value)) {
                        $items[$key] = $value;
                    } else {
                        if ($value instanceof \SimpleXMLElement) {
                            $items[$key] = $this->exportData($value);
                        }
                    }
                }
            }
        } else {
            if (sizeof($child) > 0) {
                foreach ($child as $key => $value) {
                    if (!is_array($value) && !is_object($value)) {
                        $items[$key] = $value;
                    } else {
                        if (is_object($value)) {
                            $items[$key] = $this->exportData($value);
                        } else {
                            foreach ($value as $subKey => $subValue) {
                                if (!is_object($subValue)) {
                                    $items[$responseXml->getName()][$subKey] = $subValue;
                                } else {
                                    if ($subValue instanceof \SimpleXMLElement) {
                                        $items[$responseXml->getName()][$subKey] = $this->exportData($subValue);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $items;
    }
}