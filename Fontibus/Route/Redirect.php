<?php
namespace Fontibus\Route;

class Redirect {

    /**
     * Redirect to route with optional variables
     * @param string $name
     * @param array $variable
     * @throws \Exception
     */
    public static function route(string $name, array $variable = []) {
        $url = route($name, $variable);
        header('Location: '.$url);
        exit;
    }

    /**
     * Redirect to previous page
     */
    public static function back() {
        $url = $_SERVER['HTTP_REFERER'];
        header('Location: '.$url);
        exit;
    }

}