<?php
namespace Fontibus\Route;

use App\Kernel;
use Exception;
use Fontibus\Facades\Auth;
use Fontibus\View\View;

class Pipeline {

    /**
     * Initiate Pipeline
     * @throws Exception
     */
    public static function init() {
        Router::init();
        Auth::check();
        $route = Router::getRoute();
        foreach (Kernel::$default as $middleware)
            if(!call_user_func([$middleware, 'route'], $route))
                throw new Exception('Something went wrong', 500);

        $view = $route->performAction();
        if(empty($view) || !($view instanceof View))
            throw new Exception('Unable to handle request!',500);

        $view->render();
        self::prepareSession();
    }

    /**
     * Prepare Session Data
     */
    private static function prepareSession(): void {
        foreach(session()->all() as $key => $value) {
            if($value['temp']) {
                if(!array_key_exists('used', $value))
                    continue;

                if($value['used'])
                    continue;

                $value['used'] = true;
            }

            $_SESSION[$key] = $value;
        }
    }

}