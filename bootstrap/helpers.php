<?php

use App\Kernel\VarDumper;

if (!function_exists('d')) {
    /**
     * Dump the passed variables
     *
     * @param  mixed
     * @return void
     */
    function d()
    {
        array_map(
            function ($x) {
                (new VarDumper())->dump($x);
            },
            func_get_args()
        );
    }
}

if (!function_exists('dd')) {
    /**
     * Dump the passed variables
     *
     * @param  mixed
     * @return void
     */
    function dd()
    {
        array_map(
            function ($x) {
                (new VarDumper)->dump($x);
            },
            func_get_args()
        );

        die();
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        $strLen = strlen($value);

        if ($strLen > 1 && $value[0] === '"' && $value[$strLen - 1] === '"') {
            return substr($value, 1, -1);
        }

        return $value;
    }
}

if (!function_exists('base_path')) {
    /**
     * Get the path to the base folder
     *
     * @return string
     */
    function base_path()
    {
        return dirname(__DIR__);
    }
}

if (!function_exists('app_path')) {
    /**
     * Get the path to the application folder
     *
     * @return string
     */
    function app_path()
    {
        return base_path() . '/app';
    }
}

if (!function_exists('config_path')) {
    /**
     * Get the path to the config folder
     *
     * @return string
     */
    function config_path()
    {
        return base_path() . '/config';
    }
}

if (!function_exists('public_path')) {
    /**
     * Get the path to the public folder
     *
     * @return string
     */
    function public_path()
    {
        return base_path() . '/public';
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the path to the storage folder
     *
     * @return string
     */
    function storage_path()
    {
        return base_path() . '/storage';
    }
}

if (!function_exists('str_generate')) {
    /**
     * Generate random string with specified characters
     * 
     * @param int $length
     * @param string $chars
     * @return string
     */
    function str_generate($length, $chars)
    {
        return substr(str_shuffle(str_repeat($chars, $length)), 0, $length);
    }
}

if (!function_exists('str_random')) {
    /**
     * Generate random string with specified length
     * 
     * @param int $length 
     * @return string
     */
    function str_random($length)
    {
        return str_generate($length, '0123456789ABCDEDFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
    }
}

if (!function_exists('get_months')) {
    /**
     * Month list
     * 
     * @return array
     */
    function get_months()
    {
        return array_combine(range(1, 12), [
            'Январь',
            'Февраль',
            'Март',
            'Апрель',
            'Май',
            'Июнь',
            'Июль',
            'Август',
            'Сентябрь',
            'Октябрь',
            'Ноябрь',
            'Декабрь',
        ]);
    }
}

if (!function_exists('get_month')) {
    /**
     * Month cyrillic name
     * 
     * @return string
     */
    function get_month($number)
    {
        $months = get_months();

        $number = $number % 12;
        $number = ($number == 0) ? 12 : $number;

        return isset($months[$number]) ? $months[$number] : null;
    }
}

if (!function_exists('base64urlencode')) {
    /**
     * Convert string to base64
     * 
     * @param string $str
     * @return string
     */
    function base64urlencode($str)
    {
        return strtr(base64_encode($str), '+/=', '-_,');
    }
}

if (!function_exists('base64urldecode')) {
    /**
     * Convert base64 to string
     * 
     * @param string $str
     * @return string
     */
    function base64urldecode($str)
    {
        return base64_decode(strtr($str, '-_,', '+/='));
    }
}