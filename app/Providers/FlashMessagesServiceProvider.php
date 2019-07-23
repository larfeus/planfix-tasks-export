<?php

namespace App\Providers;

use Slim\Flash\Messages;
use App\Contracts\ServiceProvider;

class FlashMessagesServiceProvider implements ServiceProvider
{
    /**
     * Service register name
     * 
     * @return string
     */
    public function name()
    {
        return 'flash';
    }

    /**
     * Register new service on dependency container
     * 
     * @return \Closure
     */
    public function register()
    {
        return function ($container) {
            return new Messages();
        };
    }
}