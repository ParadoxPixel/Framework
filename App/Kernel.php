<?php
namespace App;

class Kernel {

    public static array $middleware = [
        'auth' => \App\Middleware\AuthMiddleware::class,
        'guest' => \App\Middleware\GuestMiddleware::class
    ];

    public static array $default = [
        \App\Middleware\CSRFMiddleware::class
    ];

}