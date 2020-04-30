<?php
namespace Fontibus\Cookie;

use Exception;
use Fontibus\Hash\Hash;

class Cookie {

    /**
     * Set cookie with name, value and time valid
     * @param string $name
     * @param $value
     * @param int $expires
     */
    public static function setCookie(string $name, $value, int $expires = 1): void {
        if(is_array($value))
            $value = json_encode($value);

        $hash = Hash::encrypt($value, env('KEY', ''), true);
        setcookie($name, $hash, time() + $expires);
    }

    /**
     * Has cookie with name
     * @param string $name
     * @return bool
     */
    public static function hasCookie(string $name): bool {
        return isset($_COOKIE[$name]);
    }

    /**
     * Get cookie with name
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    public static function getCookie(string $name) {
        if(!isset($_COOKIE[$name]))
            throw new Exception('No cookie with name: '.$name, 500);

        $value = Hash::decrypt($_COOKIE[$name], env('KEY', ''), true);
        $json = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE)
            return $json;

        return $value;
    }

    /**
     * Remove cookie with name
     * @param string $name
     */
    public static function clearCookie(string $name): void {
        setcookie($name, '', -1);
    }

}