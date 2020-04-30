<?php
namespace Fontibus\Query;

use Fontibus\Collection\Collection;
use Fontibus\Query\Schema\Schema;
use PDO;

class DB {

    private static Processor $instance;

    /**
     * Initiate database class
     * @param array $settings
     */
    public static function init(array $settings) {
        self::$instance = new Processor($settings);
    }

    /**
     * Get raw data object
     * @param string $value
     * @return RawData
     */
    public static function raw(string $value): RawData {
        return new RawData($value);
    }

    /**
     * Prepare query builder for table
     * @param string $table
     * @return QueryBuilder
     */
    public static function table(string $table): QueryBuilder {
        return new QueryBuilder(self::$instance, $table);
    }

    /**
     * Prepare table builder
     * @return Schema
     */
    public static function schema(): Schema {
        return new Schema(self::$instance);
    }

    /**
     * Clean input
     * @param string $input
     * @return mixed|string
     */
    public static function sanitize(string $input) {
        return sanitize($input);
    }

    /**
     * Add quote's to input
     * @param $input
     * @return array
     */
    public static function quote($input) {
        if(is_array($input)) {
            for($i = 0; $i < count($input); $i++)
                $input[$i] = self::quote($input[$i]);

            return $input;
        }

        return self::$instance->quote(self::sanitize($input));
    }

    /**
     * Quote column
     * @param $column
     * @return array|string
     */
    public static function quoteColumn($column) {
        if(is_array($column)) {
            for($i = 0; $i < count($column); $i++)
                $column[$i] = self::quoteColumn($column[$i]);

            return $column;
        }

        $args = explode('.', $column);
        if(!is_array($args))
            return '`'.$args.'``';

        $prepared = [];
        foreach($args as $arg) {
            if($arg !== '*')
                $arg = '`' . $arg . '`';

            array_push($prepared, $arg);
        }

        return implode('.', $prepared);
    }

    /**
     * Perform raw query
     * @param string $query
     * @param array $vars
     * @return Collection
     */
    public static function query(string $query, array $vars = []): Collection {
        $stmt = self::$instance->query($query, $vars);
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        unset($stmt);
        return new Collection($result);
    }

}