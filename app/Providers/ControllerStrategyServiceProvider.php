<?php

namespace App\Providers;

use App\Kernel\ControllerStrategy;
use App\Contracts\ServiceProvider;

class ControllerStrategyServiceProvider implements ServiceProvider
{
    /**
     * Service register name
     * 
     * @return string
     */
    public function name()
    {
        return 'foundHandler';
    }

    /**
     * Register new service on dependency container
     * 
     * @return \Closure
     */
    public function register()
    {
        return function ($container) {
            return new ControllerStrategy();
        };
    }
}