<?php
namespace Fontibus\Environment;

class Env {

    private static array $array = [];

    /**
     * Load .env file
     *
     * @return void
     */
    public static function init(): void {
        if(!empty($array))
            return;

        $path = root_path().'/.env';
        if(file_exists($path)) {
            $fn = fopen($path, 'r');
            while (!feof($fn)) {
                $result = fgets($fn);
                if (empty($result))
                    continue;

                $result = str_replace("\r\n", '', $result);
                $data = explode('=', $result);
                if (count($data) < 2)
                    continue;

                self::$array[trim($data[0])] = trim($data[1], '""');
            }

            fclose($fn);
        }
    }

    /**
     * Return key from .env file otherwise the default value
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    public static function get(string $key, string $default = ''): string {
        if(isset(self::$array[$key]))
            return self::$array[$key];

        return $default;
    }

}