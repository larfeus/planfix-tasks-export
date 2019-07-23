<?php

namespace App\Contracts;

interface ServiceProvider
{
    /**
     * Service register name
     */
    public function name();
    
    /**
     * Register new service on dependency container
     */
    public function register();
}