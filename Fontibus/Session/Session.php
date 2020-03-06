<?php
namespace Fontibus\Session;

use ArrayIterator;
use IteratorAggregate;

class Session implements IteratorAggregate {

    private static Session $session;
    public static function init(): void { self::$session = new Session(); }
    public static function getSession(): Session { return self::$session; }

    private array $array;

    public function __construct() {
        $this->array = $_SESSION;
    }

    /**
     * Get iterable
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator {
        return new ArrayIterator($this->array);
    }

    /**
     * Get iterable
     *
     * @return iterable
     */
    public function all(): iterable {
        return $this->array;
    }

    /**
     * Has key
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool {
        return array_key_exists($key, $this->array);
    }

    /**
     * Get key
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key) {
        return $this->array[$key]['value'];
    }

    /**
     * Flash key
     *
     * @param string $key
     * @param string $value
     */
    public function flash(string $key, string $value): void {
        $this->array[$key] = [
            'value' => $value,
            'temp' => true,
            'used' => false
        ];
    }

    /**
     * Keep key
     *
     * @param string $key
     */
    public function keep($key): void {
        if(is_array($key)) {
            foreach($key as $value)
                if(array_key_exists($value, $this->array))
                    $this->array[$value]['used'] = false;
        } else {
            if(array_key_exists($key, $this->array))
                $this->array[$key]['used'] = false;
        }
    }

    /**
     * Set key
     *
     * @param string $key
     * @param string $value
     */
    public function set(string $key, string $value): void {
        $this->array[$key] = [
            'value' => $value,
            'temp' => false,
        ];
    }

}