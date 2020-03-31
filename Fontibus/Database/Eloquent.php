<?php
namespace Fontibus\Database;

use Exception;
use Fontibus\Collection\Collection;
use InvalidArgumentException;
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

    public function update($data): bool {
        $array = [];
        foreach($this->fillable as $field)
            $array[$field] = $data[$field];

        if($this->timestamp)
            $array['updated_at'] = date("Y-m-d H:i:s");

        return (bool) DB::table($this->getTable())->where($this->key, $data[$this->key])->update($array);
    }

    public function delete(): bool {
        return DB::table($this->getTable())->where($this->key, $this->{$this->key})->delete();
    }

    private static function database(): DB {
        $instance = new static();
        return DB::table($instance->getTable())
            ->type(PDO::FETCH_KEY_PAIR)
            ->argument(get_called_class());
    }

    private static function cast($destination, stdClass $source) {
        $class = new $destination();
        foreach($source as $key => $value)
            $class->$key = $value;

        return $class;
    }

}