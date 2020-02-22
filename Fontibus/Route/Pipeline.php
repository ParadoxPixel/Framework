<?php
namespace Fontibus\Route;

use Exception;
use Fontibus\Facades\Auth;
use Fontibus\View\View;

class Pipeline {

    public static function init() {
        Router::init();
        Auth::check();
        $route = Router::getRoute();
        $view = $route->performAction();
        if(empty($view) || !($view instanceof View))
            throw new Exception('Unable to handle request!',500);

        $view->render();
    }

}