<?php
namespace Fontibus\Query;

use Fontibus\Collection\Collection;
use PDO;

class DB {

    private static Processor $instance;

    public static function init(array $settings) {
        self::$instance = new Processor($settings);
    }

    public static function raw(string $value): RawData {
        return new RawData($value);
    }

    public static function table(string $table): QueryBuilder {
        return new QueryBuilder(self::$instance, $table);
    }

    public static function sanitize(string $input) {
        return sanitize($input);
    }

    public static function quote($input) {
        if(is_array($input)) {
            for($i = 0; $i < count($input); $i++)
                $input[$i] = self::quote($input[$i]);

            return $input;
        }

        return self::$instance->quote(self::sanitize($input));
    }

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

    public static function query(string $query, array $vars = []): Collection {
        $stmt = self::$instance->query($query, $vars);
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        unset($stmt);
        return new Collection($result);
    }

}