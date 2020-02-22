<?php
namespace Fontibus\Facades;

use Fontibus\Database\DB;
use Fontibus\String\Str;

class Validator {

    private bool $passed = false;

    public function __construct(array $data, array $settings) {
        foreach($settings as $key => $value) {
            if(empty($value) || !is_array($value))
                continue;

            $required = false;
            foreach($value as $setting) {
                if($setting === 'required') {
                    if (!array_key_exists($key, $data))
                        return;

                    $required = true;
                    continue;
                } else if($setting === 'nullable')
                    continue;

                if (!array_key_exists($key, $data))
                    break;

                if(Str::startsWith($setting, 'regex:')) {
                    $str = explode(':', $data[$key]);
                    if(count($str) < 2)
                        return;

                    $regex = $str[1];
                    if(!preg_match('/^(' . $regex . ')$/', $data[$key]))
                        return;

                    continue;
                }

                if(Str::startsWith($setting, 'same:')) {
                    $str = explode(':', $data[$key]);
                    if(count($str) < 2)
                        return;

                    $field = $str[1];
                    if(array_key_exists($field, $data))
                        return;

                    if($data[$key] !== $data[$field])
                        return;

                    continue;
                }

                if(Str::startsWith($setting, 'min:') || Str::startsWith($setting, 'max:')) {
                    $str = explode(':', $setting);
                    if(count($str) < 2)
                        return;

                    $size = $str[1];
                    if($str[0] === 'min')
                        if(strlen($data[$key]) < $size)
                            return;

                    if($str[0] === 'max')
                        if(strlen($data[$key]) > $size)
                            return;

                    continue;
                }

                if(Str::startsWith($setting, 'unique:')) {
                    $str = explode(':', $setting);
                    if(count($str) < 2)
                        return;

                    $table = $str[1];
                    $result = DB::table($table)->where($key, '=', $data[$key])->count($key, 'count')->first();
                    if(empty($result))
                        return;

                    if($result->count > 0)
                        return;

                    continue;
                }

                $result = $this->checkType($setting, $data[$key]);
                if($result === false)
                    return;
            }

            if($required && !array_key_exists($key, $data))
                return;
        }

        $this->passed = true;
    }

    public function passed(): bool {
        return $this->passed;
    }

    public static function checkType($setting, $value): bool {
        switch ($setting) {
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL);

            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);

            case 'password':
                return preg_match('/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%?]).*$/', $value);
        }
    }

}