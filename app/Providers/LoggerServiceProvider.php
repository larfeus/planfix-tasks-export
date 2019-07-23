<?php

namespace App\Providers;

use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Handler\StreamHandler;
use App\Contracts\ServiceProvider;

class LoggerServiceProvider implements ServiceProvider
{
    /**
     * Service register name
     * 
     * @return string
     */
    public function name()
    {
        return 'logger';
    }

    /**
     * Register new service on dependency container
     * 
     * @return \Closure
     */
    public function register()
    {
        return function ($container) {
            
            $config = require config_path() . '/monolog.php';

            $logger = new Logger($config['name']);

            $logger->pushProcessor(new UidProcessor());
            $logger->pushHandler(new StreamHandler($config['path'], Logger::DEBUG));

            return $logger;
        };
    }
}