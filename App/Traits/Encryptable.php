<?php
namespace App\Traits;

use Fontibus\Hash\Hash;

trait Encryptable {

    private array $array = [];
    public function getAttribute(string $name) {
        if(in_array($name, $this->encryptable)) {
            if(array_key_exists($name, $this->array))
                return $this->array[$name];

            $decrypt = Hash::decrypt(parent::getAttribute($name), env('KEY', ''), true);
            $this->array[$name] = $decrypt;
            return $decrypt;
        }

        parent::getAttribute($name);
    }

    public function setAttribute(string $name, $value) {
        if(!parent::hasAttribute($name)) {
            parent::setAttribute($name, $value);
            return;
        }

        if(in_array($name, $this->encryptable)) {
            $this->array[$name] = $value;
            parent::setAttribute($name, Hash::encrypt($value, env('KEY', ''), true));
            return;
        }

        parent::setAttribute($name, $value);
    }

}