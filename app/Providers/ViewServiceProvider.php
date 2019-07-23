<?php

namespace App\Providers;

use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Twig_SimpleFunction;
use App\Contracts\ServiceProvider;

class ViewServiceProvider implements ServiceProvider
{
    /**
     * {@inheritDocs}
     */
    public function name()
    {
        return 'view';
    }

    /**
     * {@inheritDocs}
     */
    public function register()
    {
        return function ($container) {

            $config = require config_path() . '/twig.php';

            $view = new Twig(
                base_path() . '/views',
                $config
            );

            $view->addExtension(
                new TwigExtension(
                    $container->router,
                    $container->request->getUri()
                )
            );

            $environment = $view->getEnvironment();
            $environment->addGlobal('flash', $container->flash);

            $environment->addFunction(
                new Twig_SimpleFunction('debug', function() use($container) {
                    d($container->session->all());
                })
            );

            $environment->addFunction(
                new Twig_SimpleFunction('env', function($name) use($container) {
                    return env('APP_ENV') == $name;
                })
            );

            return $view;
        };
    }
}