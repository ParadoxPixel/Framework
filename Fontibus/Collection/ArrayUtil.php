<?php
namespace Fontibus\Collection;

class ArrayUtil {

    public static function getKeys(array $value) {
        $keys = [];
        foreach($value as $key => $val)
            array_push($keys, $key);

        return $keys;
    }

    public static function getValues(array $value) {
        $values = [];
        foreach($value as $key => $val)
            array_push($values , $val);

        return $values ;
    }

    public static function splitArray(array $value) {
        $split = [
            'keys' => [],
            'values' => [[]]
        ];

        foreach ($value as $key => $val) {
            array_push($split['keys'], $key);
            array_push($split['values'][0], $val);
        }

        return $split;
    }

    public static function isMulti($a) {
        foreach ($a as $key => $value)
            if(is_array($value))
                return true;

        return false;
    }

}