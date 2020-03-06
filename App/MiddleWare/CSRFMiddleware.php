<?php
namespace App\Middleware;

use Exception;
use Fontibus\Facades\Middleware;
use Fontibus\Route\Route;

class CSRFMiddleware extends Middleware {

    public static function route(Route $route) {
        $session = session();
        if($route->getMethod() === "POST") {
            if(!$session->has('csrf-token')) {
                throw new Exception("Page expired", 404);
            } else {
                if(!isset($_POST['csrf-token']))
                    throw new Exception("Page expired", 404);

                if(!hash_equals($session->get('csrf-token'), $_POST['csrf-token']))
                    throw new Exception("Page expired", 404);
            }
        } else {
            session()->flash('csrf-token', bin2hex(random_bytes(32)));
        }

        return true;
    }

}