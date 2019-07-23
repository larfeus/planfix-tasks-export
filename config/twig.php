<?php

return [
    'debug' 			=> env('APP_DEBUG', false),
    'auto_reload' 		=> env('APP_DEBUG', false),
    'strict_variables' 	=> env('APP_DEBUG', false),
    'cache' 			=> env('APP_DEBUG', false) ? false : storage_path() . '/cache/twig',
];