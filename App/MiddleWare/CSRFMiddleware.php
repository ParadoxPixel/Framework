<?php
namespace App\Middleware;

use Exception;
use Fontibus\Facades\Middleware;
use Fontibus\Route\Route;

class CSRFMiddleware extends Middleware {

    public static function route(Route $route) {
        $session = session();
        if($route->getMethod() === "POST") {
            if(!$session->has('X-CSRF')) {
                throw new Exception("Page expired", 404);
            } else {
                if(!isset($_POST['X-CSRF']))
                    throw new Exception("Page expired", 404);

                if(!hash_equals($session->get('X-CSRF'), $_POST['X-CSRF']))
                    throw new Exception("Page expired", 404);
            }
        } else {
            try {
                session()->flash('X-CSRF', bin2hex(random_bytes(32)));
            } catch (Exception $e) {
                throw new Exception("Something went wrong", 500);
            }
        }

        return true;
    }

}