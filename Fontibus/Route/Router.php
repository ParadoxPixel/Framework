<?php
namespace Fontibus\Route;

use Exception;
use Fontibus\String\Str;

class Router {

    private static array $routes = [];

    private static string $group = '';
    private static string $namespace = '';

    public static function init() {
        if ($handle = opendir(root_path().DIRECTORY_SEPARATOR.'routes')) {
            while (false !== ($file = readdir($handle))) {
                if ('.' === $file || '..' === $file)
                    continue;

                if(!Str::endsWith($file, '.php'))
                    continue;

                include root_path().DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.$file;
            }

            closedir($handle);
        }
    }

    /**
     * Register GET request
     *
     * @param string $path
     * @param $class
     * @return Route
     */
    public static function get(string $path, $class): Route {
        if(!Str::endsWith(self::$group, '/') && !Str::startsWith($path, '/'))
            $path = '/'.$path;

        if(!is_callable($class)) {
            if (!Str::endsWith(self::$namespace, '\\') && !Str::startsWith($class, '\\'))
                $class = '\\' . $class;

            $class = self::$namespace.$class;
        }

        $route = new Route('GET', self::$group.$path, $class);
        array_push(self::$routes, $route);
        return $route;
    }

    /**
     * Register POST request
     *
     * @param string $path
     * @param $class
     * @return Route
     */
    public static function post(string $path, $class): Route {
        if(!Str::endsWith(self::$group, '/') && !Str::startsWith($path, '/'))
            $path = '/'.$path;

        if(!is_callable($class)) {
            if (!Str::endsWith(self::$namespace, '\\') && !Str::startsWith($class, '\\'))
                $class = '\\' . $class;

            $class = self::$namespace.$class;
        }

        $route = new Route('POST', self::$group.$path, $class);
        array_push(self::$routes, $route);
        return $route;
    }

    /**
     * Register on ANY method
     *
     * @param string $path
     * @param $class
     * @return Route
     */
    public static function any(string $path, $class): Route {
        if(!Str::endsWith(self::$group, '/') && !Str::startsWith($path, '/'))
            $path = '/'.$path;

        if(!is_callable($class)) {
            if (!Str::endsWith(self::$namespace, '\\') && !Str::startsWith($class, '\\'))
                $class = '\\' . $class;

            $class = self::$namespace.$class;
        }

        $route = new Route('ANY', self::$group.$path, $class);
        array_push(self::$routes, $route);
        return $route;
    }

    /**
     * Register request group
     *
     * @param string $path
     * @param $function
     * @param string $namespace
     * @return void
     */
    public static function group(string $path, $function, string $namespace = ''): void {
        if(!is_callable($function))
            return;

        if(!Str::startsWith($path, '/') && !Str::endsWith(self::$group, '/'))
            $path = '/'.$path;

        if(!Str::startsWith($namespace, '\\') && !Str::endsWith(self::$namespace, '\\'))
            $namespace = '\\'.$namespace;

        $before_group = self::$group;
        $before_namespace = self::$namespace;
        self::$group = $before_group.$path;
        self::$namespace = $before_namespace.$namespace.'\\';

        $function();

        self::$group = $before_group;
        self::$namespace = $before_namespace;
    }

    public static function getRoute(): Route {
        $method = url()->getMethod();
        $request = url()->getRequest();
        foreach(self::$routes as $route)
            if ($route->getMethod() == $method || $route->getMethod() == 'ANY')
                if($route->matchRequest($request))
                    return $route;

        throw new Exception('Unknown request!', 404);
    }

    public static function route(string $name) {
        if(empty($name))
            return null;

        foreach(self::$routes as $route)
            if($route->getName() === $name)
                return $route->getPath();

        return null;
    }

}