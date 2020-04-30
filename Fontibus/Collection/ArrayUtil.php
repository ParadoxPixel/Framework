<?php
namespace Fontibus\Collection;

class ArrayUtil {

    /**
     * Get keys from multidimensional array
     * @param array $value
     * @return array
     */
    public static function getKeys(array $value) {
        $keys = [];
        foreach($value as $key => $val)
            array_push($keys, $key);

        return $keys;
    }

    /**
     * Get values from multidimensional array
     * @param array $value
     * @return array
     */
    public static function getValues(array $value) {
        $values = [];
        foreach($value as $key => $val)
            array_push($values , $val);

        return $values ;
    }

    /**
     * Split multidimensional array in keys and values array
     * @param array $value
     * @return array
     */
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

    /**
     * Check if array is multidimensional
     * @param $array
     * @return bool
     */
    public static function isMulti($array) {
        foreach ($array as $key => $value)
            if(is_array($value))
                return true;

        return false;
    }

}