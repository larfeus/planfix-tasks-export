<?php

namespace App\Providers;

use App\Services\AuthService;
use App\Contracts\ServiceProvider;

class AuthServiceProvider implements ServiceProvider
{
    /**
     * Service register name
     * 
     * @return string
     */
    public function name()
    {
        return 'auth';
    }

    /**
     * Register new service on dependency container
     * 
     * @return \Closure
     */
    public function register()
    {
        return function ($container) {
            return new AuthService($container);
        };
    }
}