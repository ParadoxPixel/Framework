<?php
namespace Fontibus\Facades;

use App\Kernel;
use Exception;
use Fontibus\Route\Route;

class Controller {

    private array $array = [];

    public function middleware(array $keys) {
        foreach($keys as $key) {
            if (!isset(Kernel::$middleware[$key]))
                throw new Exception('Undefined middleware with key: ' . $key, 500);

            $middleware = Kernel::$middleware[$key];
            if (!is_subclass_of($middleware, '\Fontibus\Facades\Middleware'))
                throw new Exception('Invalid middleware', 500);

            array_push($this->array, $middleware);
        }
    }

    public function checkMiddleware(Route $route) {
        foreach($this->array as $middleware) {
            $bool = call_user_func([$middleware, 'route'], $route);
            if(!is_bool($bool))
                return $bool;
        }

        return true;
    }

}