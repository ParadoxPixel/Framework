<?php
namespace App\Middleware;

use Fontibus\Facades\Auth;
use Fontibus\Facades\Middleware;
use Fontibus\Route\Redirect;
use Fontibus\Route\Route;

class AuthMiddleware extends Middleware {

    public static function route(Route $route) {
        if(Auth::isGuest())
           return Redirect::route('login');

        return true;
    }

}