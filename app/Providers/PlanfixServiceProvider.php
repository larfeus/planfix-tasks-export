<?php

namespace App\Providers;

use App\Services\PlanfixService;
use App\Contracts\ServiceProvider;

class PlanfixServiceProvider implements ServiceProvider
{
    /**
     * Service register name
     * 
     * @return string
     */
    public function name()
    {
        return 'planfix';
    }

    /**
     * Register new service on dependency container
     * 
     * @return \Closure
     */
    public function register()
    {
        return function ($container) {
            return new PlanfixService($container);
        };
    }
}