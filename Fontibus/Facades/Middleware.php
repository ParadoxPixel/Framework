<?php
namespace Fontibus\Facades;

use Fontibus\Route\Route;

abstract class Middleware {

    public abstract static function route(Route $route);

}