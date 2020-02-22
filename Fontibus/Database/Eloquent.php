<?php
namespace Fontibus\Database;

use Exception;
use Fontibus\Collection\Collection;
use PDO;
use ReflectionObject;
use stdClass;

class Eloquent {

    /**
     * The name of the table
     *
     * @var string
     */
    protected string $table;

    /**
     * Has primary key
     *
     * @var bool
     */
    protected bool $primary_key = true;

    /**
     * The name of the search column
     *
     * @var string
     */
    protected string $key = 'id';

    /**
     * Has timestamp
     *
     * @var bool
     */
    protected bool $timestamp = true;

    /**
     * Fields that can be updated
     *
     * @var array
     */
    protected array $fillable = [];

    protected function getTable(): string {
        return $this->table;
    }

    public static function getBaseName(): string {
        $class = get_called_class();
        $array = explode('\\', $class);
        return array_pop($array);
    }

    public static function all(): Collection {
        $result = self::database()->get();
        if(empty($result))
            return new Collection([]);

        return $result;
    }

    public static function find($key) {
        $instance = new static();
        $result = self::database()->where($instance->key, $key)->first();
        if(empty($result))
            return null;

        return self::cast(get_called_class(), $result);
    }

    public static function findOrFail($key) {
        $instance = new static();
        $result = self::database()->where($instance->key, $key)->first();
        if(empty($result))
            throw new Exception('No '.self::getBaseName().' with '.$instance->key.': '.$key, 404);

        return self::cast(get_called_class(), $result);
    }

    public static function where($where, string $op = null, string $val = null, string $type = '', string $andOr = 'AND'): DB {
        return self::database()->where($where, $op, $val, $type, $andOr);
    }

    public static function select($fields): DB {
        return self::database()->select($fields);
    }

    public function save(): bool {
        $array = [];
        foreach($this->fillable as $field)
            $array[$field] = $this->{$field};

        return (bool) DB::table($this->getTable())->where($this->key, $this->{$this->key})->update($array);
    }

    public function delete(): bool {
        return DB::table($this->getTable())->where($this->key, $this->{$this->key})->delete();
    }

    private static function database(): DB {
        $instance = new static();
        return DB::table($instance->getTable())
            ->type(PDO::FETCH_CLASS)
            ->argument(get_called_class());
    }

    private static function cast($destination, $sourceObject) {
        if (is_string($destination)) {
            $destination = new $destination();
        }

        $sourceReflection = new ReflectionObject($sourceObject);
        $destinationReflection = new ReflectionObject($destination);
        $sourceProperties = $sourceReflection->getProperties();
        foreach ($sourceProperties as $sourceProperty) {
            $sourceProperty->setAccessible(true);
            $name = $sourceProperty->getName();
            $value = $sourceProperty->getValue($sourceObject);
            if ($destinationReflection->hasProperty($name)) {
                $propDest = $destinationReflection->getProperty($name);
                $propDest->setAccessible(true);
                $propDest->setValue($destination,$value);
            } else {
                $destination->$name = $value;
            }
        }

        return $destination;
    }

}