<?php
error_reporting(0);
try {
    require_once '../autoload.php';
    \Fontibus\Route\Pipeline::init();
} catch(Exception $e) {
    if(!filter_var(env('DEBUG', 'TRUE'), FILTER_VALIDATE_BOOLEAN))
        $e = new Exception('Unable to Handle Request', 500);

    handle_exception($e);
    exit;
}