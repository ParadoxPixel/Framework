<?php
namespace Fontibus\Facades;

use Exception;

class Logger {

    public static function write(string $message) {
        $data = $message.PHP_EOL;
        try {
            $path = storage_path().DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'log-'.date('d-m-Y');
            $fp = fopen($path, 'a');
            fwrite($fp, $data);
            fclose($fp);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

}