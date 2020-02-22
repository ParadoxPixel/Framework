<?php
namespace Fontibus\String;

class Str {

    public static function startsWith(string $haystack, string $needle): bool {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    public static function endsWith(string $haystack, string $needle): bool {
        $length = strlen($needle);
        if ($length == 0)
            return true;

        return (substr($haystack, -$length) === $needle);
    }

    public static function random(int $length = 10, string $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string {
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++)
            $randomString .= $characters[rand(0, $charactersLength - 1)];

        return $randomString;
    }

}