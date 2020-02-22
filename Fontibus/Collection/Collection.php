<?php
namespace Fontibus\Collection;

use ArrayIterator;
use IteratorAggregate;

class Collection implements IteratorAggregate {

    private array $array;

    public function __construct(array $array = array()) {
        $this->array = $array;
    }

    /**
     * Get iterable
     *
     * @return iterable
     */
    public function getIterator(): iterable {
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
        return $this->array[$key];
    }

    /**
     * Order descending by certain key
     *
     * @param string $field
     * @return Collection
     */
    public function pluck(string $field): Collection {
        $this->array = array_column($this->array, $field);
        return $this;
    }

    /**
     * Skip data in collection
     *
     * @param int $amount
     * @return Collection
     */
    public function skip(int $amount): Collection {
        if($amount <= 0)
            return $this;

        array_slice($this->array, $amount);
        return $this;
    }

    /**
     * Take limited data in collection
     *
     * @param int $amount
     * @return Collection
     */
    public function take(int $amount): Collection {
        if($amount <= 0 || $amount >= count($this->array))
            return $this;

        array_slice($this->array, 0, $amount);
        return $this;
    }

    /**
     * Return collection where certain key value pairs
     *
     * @param array $args
     * @return Collection
     */
    public function where(array $args): Collection {
        foreach($args as $key => $value)
            $this->array = array_filter($this->array, function($var) use ($key,$value) {
                return ($var->{$key} === $value);
            });

        return $this;
    }

    /**
     * Order by certain key
     *
     * @param string $key
     * @return Collection
     */
    public function orderBy(string $key): Collection {
        usort($this->array, function($a, $b) use ($key) {
            return strnatcmp($a->{$key}, $b->{$key});
        });

        return $this;
    }

    /**
     * Order descending by certain key
     *
     * @param string $key
     * @return Collection
     */
    public function orderByDesc(string $key): Collection {
        usort($this->array, function($a, $b) use ($key) {
            return !strnatcmp($a->{$key}, $b->{$key});
        });

        return $this;
    }

}