<?php
namespace Fontibus\Model;

use Fontibus\Query\Eloquent\Eloquent;
use JsonSerializable;

class Model extends Eloquent implements JsonSerializable {

    private array $properties = [];

    /**
     * Call method on $model->$name
     * @param string $name
     * @return mixed|string
     */
    public function __get(string $name) {
        return $this->getAttribute($name);
    }

    /**
     * Call method on $model->$name = $value
     * @param string $name
     * @param mixed $value
     * @return mixed|string
     */
    public function __set(string $name, $value) {
        $this->setAttribute($name, $value);
    }

    /**
     * Return key value array
     * @return mixed|string
     */
    public function __serialize() {
        return $this->properties;
    }

    /**
     * Set key value array
     * @param array $properties
     * @return mixed|string
     */
    public function __unserialize(array $properties) {
        $this->properties = $properties;
    }

    /**
     * Get value of attribute
     * @param string $name
     * @return mixed|string
     */
    public function getAttribute(string $name) {
        $method = $this->makeAttributeMethod('get', $name);
        if(method_exists($this, $method))
            return $this->$method();

        if(!isset($this->properties[$name]))
            return '';

        return $this->properties[$name];
    }

    /**
     * Set attribute with name and value
     * @param string $name
     * @param $value
     */
    public function setAttribute(string $name, $value) {
        $method = $this->makeAttributeMethod('set', $name);
        if(method_exists($this, $method)) {
            $this->$method($value);
            return;
        }

        $this->properties[$name] = $value;
    }

    /**
     * Check if model has attribute
     * @param string $name
     * @return bool
     */
    public function hasAttribute(string $name): bool {
        return array_key_exists($name, $this->properties);
    }

    /**
     * Return attribute array
     * @return array
     */
    public function getAttributeArray(): array {
        return $this->properties;
    }

    /**
     * Save model
     * @return bool
     */
    public function save(): bool {
        return parent::update($this->properties);
    }

    /**
     * Get attribute method from prefix and name
     * @param string $prefix
     * @param string $name
     * @return string
     */
    private function makeAttributeMethod(string $prefix, string $name): string {
        $method = $prefix;
        $args = explode("_", $name);
        foreach ($args as $arg)
            $method .= ucfirst($arg);

        $method .= 'Attribute';
        return $method;
    }

    public function jsonSerialize() {
        return $this->properties;
    }

}