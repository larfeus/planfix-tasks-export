<?php

namespace App\Kernel;

use Dotenv\Dotenv;
use Slim\App as BaseApp;
use Slim\Views\Twig;

class App extends BaseApp
{
    /**
     * Register application environment
     */
    public function boot()
    {
        Dotenv::create(base_path())->load();
        
        $this->registerConfig();
        $this->registerServices();
        $this->registerMiddlewares();
        $this->registerRoutes();
    }

    /**
     * Register application settings
     */
    public function registerConfig()
    {
        $settings = $this->getContainer()->get('settings');
        $settings->replace(
            require config_path() . '/app.php'
        );
    }

    /**
     * Register new services on dependency container
     */
    public function registerServices()
    {
        $container = $this->getContainer();

        $services = require config_path() . '/providers.php';

        if (is_array($services) && !empty($services)) {
            foreach ($services as $service) {

                $instance = new $service();

                $container[$instance->name()] = $instance->register();
            }
        }
    }

    /**
     * Register application middlewares
     */
    public function registerMiddlewares()
    {
        $container = $this->getContainer();

        $middlewares = require config_path() . '/middlewares.php';

        $this->add(function($request, $response, $next) use ($container) {
            if ($route = $request->getAttribute('route')) {
                $container->view->getEnvironment()->addGlobal('currentRoute', $route->getName());
            }
            return $next($request, $response);
        });

        if (is_array($middlewares) && !empty($middlewares)) {
            foreach ($middlewares as $middleware) {
                $this->add($middleware);
            }
        }
    }

    /**
     * Register application routes
     */
    public function registerRoutes()
    {
        $app = $this;
        
        require base_path() . '/routes/web.php';
    }
}