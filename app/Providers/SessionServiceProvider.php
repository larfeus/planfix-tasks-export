<?php

namespace App\Providers;

use App\Services\SessionService;
use App\Contracts\ServiceProvider;

class SessionServiceProvider implements ServiceProvider
{
    /**
     * Service register name
     * 
     * @return string
     */
    public function name()
    {
        return 'session';
    }

    /**
     * Register new service on dependency container
     * 
     * @return \Closure
     */
    public function register()
    {
        return function ($container) {
            return new SessionService($container);
        };
    }
}