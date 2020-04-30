<?php
namespace Fontibus\Query\Eloquent;

use Exception;
use Fontibus\Collection\Collection;
use Fontibus\Query\DB;
use Fontibus\Query\QueryBuilder;
use PDO;

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

    /**
     * Get Table
     * @return string
     */
    protected function getTable(): string {
        return $this->table;
    }

    /**
     * Return Base Name
     * @return string
     */
    public static function getBaseName(): string {
        $class = get_called_class();
        $array = explode('\\', $class);
        return array_pop($array);
    }

    /**
     * Return All Eloquents
     * @return Collection
     */
    public static function all(): Collection {
        $result = self::database()->get();
        if(empty($result))
            return new Collection([]);

        return $result;
    }

    /**
     * Find Eloquent
     * @param $key
     * @return Collection|null
     */
    public static function find($key) {
        $instance = new static();
        $result = self::database()->where($instance->key, '=',$key)->first();
        if(empty($result) || $result == false)
            return null;

        return $result;
    }

    /**
     * Error if Eloquent not found
     * @param $key
     * @return Collection|null
     * @throws Exception
     */
    public static function findOrFail($key) {
        $instance = new static();
        $result = self::database()->where($instance->key, $key)->first();
        if(empty($result) || $result == false)
            throw new Exception('No '.self::getBaseName().' with '.$instance->key.': '.$key, 404);

        return $result;
    }

    /**
     * Where Clause
     * @param $first
     * @param null $operator
     * @param null $second
     * @param string $type
     * @return QueryBuilder
     */
    public static function where($first, $operator = null, $second = null, $type = 'and'): QueryBuilder {
        return self::database()->where($first, $operator, $second, $type);
    }

    /**
     * Select
     * @param $fields
     * @return QueryBuilder
     */
    public static function select($fields): QueryBuilder {
        return self::database()->select($fields);
    }

    /**
     * Update
     * @param $data
     * @return bool
     */
    public function update($data): bool {
        $array = [];
        foreach($this->fillable as $field)
            $array[$field] = $data[$field];

        if($this->timestamp)
            $array['updated_at'] = date("Y-m-d H:i:s");

        return (bool) DB::table($this->getTable())->where($this->key, $data[$this->key])->update($array);
    }

    /**
     * Delete
     * @return bool
     */
    public function delete(): bool {
        return DB::table($this->getTable())->where($this->key, $this->{$this->key})->delete();
    }

    /**
     * Prepare QueryBuilder
     * @return QueryBuilder
     */
    private static function database(): QueryBuilder {
        $instance = new static();
        return DB::table($instance->getTable())
            ->setFetchMode(PDO::FETCH_CLASS, get_called_class());
    }

}