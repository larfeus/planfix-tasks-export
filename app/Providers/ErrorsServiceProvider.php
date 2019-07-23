<?php

namespace App\Providers;

use App\Services\ErrorHandlerService;
use App\Contracts\ServiceProvider;

class ErrorsServiceProvider implements ServiceProvider
{
    /**
     * Service register name
     * 
     * @return string
     */
    public function name()
    {
        return 'errorHandler';
    }

    /**
     * Register new service on dependency container
     * 
     * @return \Closure
     */
    public function register()
    {
        return function ($container) {
            return new ErrorHandlerService($container);
        };
    }
}