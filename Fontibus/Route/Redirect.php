<?php
namespace Fontibus\Route;

class Redirect {

    public static function route(string $name, array $variable = []) {
        $url = route($name, $variable);
        header('Location: '.$url);
        exit;
    }

    public static function back() {
        $url = $_SERVER['HTTP_REFERER'];
        header('Location: '.$url);
        exit;
    }

}