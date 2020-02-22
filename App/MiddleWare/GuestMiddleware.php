<?php
namespace App\Middleware;

use Fontibus\Facades\Auth;
use Fontibus\Facades\Middleware;
use Fontibus\Route\Redirect;
use Fontibus\Route\Route;

class GuestMiddleware extends Middleware {

    public static function route(Route $route) {
        if(!Auth::isGuest())
           return Redirect::route('home');

        return true;
    }

}