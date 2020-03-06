<?php
define('ROOT_PATH', dirname(__FILE__));
function handle_exception(Exception $error) {
    $error_code = $error->getCode();
    $error_message = $error->getMessage();
    include_once ROOT_PATH.'/resources/error/error.php';
}

if(!function_exists('root_path')) {
    function root_path(): string {
        return ROOT_PATH;
    }
}

if(!function_exists('storage_path')) {
    function storage_path(): string {
        return ROOT_PATH.DIRECTORY_SEPARATOR.'storage';
    }
}

require_once(root_path().'/vendor/autoload.php');
spl_autoload_register(function($class_name) {
    $path = root_path().'/'.$class_name . '.php';
    if(!file_exists($path))
        return;

    require_once($path);
});

use Fontibus\Database\DB;
use Fontibus\Environment\Env;
use Fontibus\IP\IP;
use Fontibus\Session\Session;
use Fontibus\String\Str;
use Fontibus\Url\Url;
use Fontibus\View\View;

IP::init();
Env::init();
if(!function_exists('env')) {
    function env(string $key, string $default = ''): string {
        return Env::get($key, $default);
    }
}

session_start();
Session::init();
if(!function_exists('session')) {
    function session(): Session {
        return Session::getSession();
    }
}

Url::init();
if(!function_exists('url')) {
    function url(): Url {
        return new Url();
    }
}

if(!function_exists('route')) {
    function route(string $name, array $parameters = []): string {
        $path = \Fontibus\Route\Router::route($name);
        if(empty($path))
            throw new Exception('No route with name: '.$name, 500);

        return url()->route($path, $parameters);
    }
}

if(!function_exists('view')) {
    function view(string $name, array $parameters = []) {
        return new View($name, $parameters);
    }
}

if(!function_exists('asset')) {
    function asset(string $path) {
        if(!Str::startsWith($path, '/') && !Str::endsWith(url()->getBaseUrl(), '/'))
            $path = '/'.$path;

        return url()->getBaseUrl().$path;
    }
}

DB::init([
    'debug' => env('DEBUG', 'TRUE'),
    'charset' => env('CHARSET', 'utf8'),
    'useCache' => env('DB_CACHE_ENABLED', 'FALSE'),
    'cacheTime' => (int) env('DB_CACHE_TIME', '60'),
    'cacheDir' => root_path().'storage/cache/',
    'host' => env('DB_HOST', 'localhost'),
    'port' => (int) env('DB_PORT', '3306'),
    'name' => env('DB_NAME', 'database'),
    'user' => env('DB_USER', 'root'),
    'pass' => env('DB_PASS', '')
]);