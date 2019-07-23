<?php

require __DIR__ . '/../vendor/autoload.php';

clearstatcache();

error_reporting(E_ALL);

set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
});

session_start();

$app = new \App\Kernel\App();
$app->boot();
$app->run();