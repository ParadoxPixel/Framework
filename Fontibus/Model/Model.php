<?php
namespace Fontibus\Model;

use Fontibus\Database\Eloquent;

class Model extends Eloquent {

    private array $properties = [];

    public function __get(string $name) {
        return $this->getAttribute($name);
    }

    public function __set(string $name, $value) {
        $this->setAttribute($name, $value);
    }

    public function getAttribute(string $name) {
        if(!isset($this->properties[$name]))
            return '';

        return $this->properties[$name];
    }

    public function setAttribute(string $name, $value) {
        $this->properties[$name] = $value;
    }

    public function hasAttribute(string $name): bool {
        return array_key_exists($name, $this->properties);
    }

    public function getAttributeArray(): array {
        return $this->properties;
    }

    public function save(): bool {
        return parent::update($this->properties);
    }

}