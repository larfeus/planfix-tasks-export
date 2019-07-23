<?php

namespace App\Providers;

use App\Services\GoogleDriveService;
use App\Contracts\ServiceProvider;

class GoogleDriveServiceProvider implements ServiceProvider
{
    /**
     * Service register name
     * 
     * @return string
     */
    public function name()
    {
        return 'googledrive';
    }

    /**
     * Register new service on dependency container
     * 
     * @return \Closure
     */
    public function register()
    {
        return function ($container) {
            return new GoogleDriveService($container);
        };
    }
}