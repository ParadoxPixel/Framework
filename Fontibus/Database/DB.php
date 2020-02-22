<?php
namespace Fontibus\Database;

use Closure;
use Fontibus\Collection\Collection;
use Fontibus\Database\Cache\Cache;
use PDO;
use PDOException;

class DB {

    public static $pdo = null;
    protected static string $select = '*';
    protected static $from = null;
    protected static $where = null;
    protected static $limit = null;
    protected static $offset = null;
    protected static $join = null;
    protected static $orderBy = null;
    protected static $groupBy = null;
    protected static $having = null;
    protected static bool $grouped = false;
    protected static int $numRows = 0;
    protected static $insertId = null;
    protected static $query = null;
    protected static $error = null;
    protected static $result = [];
    protected static array $op = ['=', '!=', '<', '>', '<=', '>=', '<>'];
    protected static int $queryCount = 0;
    protected static bool $debug = true;
    protected static int $transactionCount = 0;
    protected static $type = null;
    protected static $argument = null;
    protected static bool $useCache = false;
    protected static int $cacheTime = 60;
    protected static $cache = null;
    protected static $cacheDir = null;
    private static $_instance = null;

    public static function init(array $settings): void {
        self::getInstance();
        self::$debug = isset($settings['debug']) && filter_var($settings['debug'], FILTER_VALIDATE_BOOLEAN) ? true : false;
        self::$cacheDir = isset($settings['cacheDir']) ? $settings['cacheDir'] : __DIR__.'/cache/';
        if(!isset($settings['charset'])) {
            $settings['charset'] = 'utf8';
        }

        if(isset($settings['useCache'])) {
            self::$useCache = filter_var($settings['useCache'], FILTER_VALIDATE_BOOLEAN);
        }

        if(isset($settings['cacheTime']) && is_numeric($settings['cacheTime'])) {
            self::$cacheTime = $settings['cacheTime'];
        }


        self::$pdo = new PDO('mysql:host='.$settings['host'].';port='.$settings['port'].';dbname='.$settings['name'].';charset='.$settings['charset'], $settings['user'], $settings['pass']);
        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    }

    private function __construct () {
        return self::$pdo;
    }

    public static function getInstance (): DB {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    public static function table($table): DB {
        if(is_array($table)) {
            $from = '';
            foreach ($table as $key) {
                $from .= $key . ', ';
            }

            self::$from = rtrim($from, ', ');
        } else {
            if (strpos($table, ',') > 0) {
                $tables = explode(',', $table);
                foreach ($tables as $key => &$value) {
                    $value = ltrim($value);
                }

                self::$from = implode(', ', $tables);
            } else {
                self::$from = $table;
            }
        }

        if(self::$useCache) {
            self::$cache = new Cache(self::$cacheDir, self::$cacheTime);
        }

        return self::getInstance();
    }

    public function type(string $type): DB {
        self::$type = $type;
        return $this;
    }

    public function argument(string $argument): DB {
        self::$argument = $argument;
        return $this;
    }

    public function select($fields): DB {
        $select = (is_array($fields) ? implode(', ', $fields) : $fields);
        self::$select = (self::$select == '*' ? $select : self::$select . ', ' . $select);
        return $this;
    }

    public function max(string $field, string $name = null): DB {
        $func = 'MAX(' . $field . ')' . (! is_null($name) ? ' AS ' . $name : '');
        self::$select = (self::$select == '*' ? $func : self::$select . ', ' . $func);
        return $this;
    }

    public function min(string $field, string $name = null): DB {
        $func = 'MIN(' . $field . ')' . (! is_null($name) ? ' AS ' . $name : '');
        self::$select = (self::$select == '*' ? $func : self::$select . ', ' . $func);
        return $this;
    }

    public function sum(string $field, string $name = null): DB {
        $func = 'SUM(' . $field . ')' . (! is_null($name) ? ' AS ' . $name : '');
        self::$select = (self::$select == '*' ? $func : self::$select . ', ' . $func);
        return $this;
    }

    public function count(string $field, string $name = null): DB {
        $func = 'COUNT(' . $field . ')' . (! is_null($name) ? ' AS ' . $name : '');
        self::$select = (self::$select == '*' ? $func : self::$select . ', ' . $func);
        return $this;
    }

    public function avg(string $field, string $name = null): DB {
        $func = 'AVG(' . $field . ')' . (! is_null($name) ? ' AS ' . $name : '');
        self::$select = (self::$select == '*' ? $func : self::$select . ', ' . $func);
        return $this;
    }

    public function join(string $table, string $field1 = null, string $op = null, string $field2 = null, string $type = ''): DB {
        $on = $field1;
        if (!is_null($op)) {
            $on = (! in_array($op, self::$op) ? $field1 . ' = ' . $op : $field1 . ' ' . $op . ' ' . $field2);
        }

        if (is_null(self::$join)) {
            self::$join = ' ' . $type . 'JOIN' . ' ' . $table . ' ON ' . $on;
        } else {
            self::$join = self::$join . ' ' . $type . 'JOIN' . ' ' . $table . ' ON ' . $on;
        }

        return $this;
    }

    public function innerJoin(string $table, string $field1, string $op = '', string $field2 = ''): DB {
        $this->join($table, $field1, $op, $field2, 'INNER ');
        return $this;
    }

    public function leftJoin(string $table, string $field1, string $op = '', string $field2 = ''): DB {
        $this->join($table, $field1, $op, $field2, 'LEFT ');
        return $this;
    }

    public function rightJoin(string $table, string $field1, string $op = '', string $field2 = ''): DB {
        $this->join($table, $field1, $op, $field2, 'RIGHT ');
        return $this;
    }

    public function fullOuterJoin(string $table, string $field1, string $op = '', string $field2 = ''): DB {
        $this->join($table, $field1, $op, $field2, 'FULL OUTER ');
        return $this;
    }

    public function leftOuterJoin(string $table, string $field1, string $op = '', string $field2 = ''): DB {
        $this->join($table, $field1, $op, $field2, 'LEFT OUTER ');
        return $this;
    }

    public function rightOuterJoin(string $table, string $field1, string $op = '', string $field2 = ''): DB {
        $this->join($table, $field1, $op, $field2, 'RIGHT OUTER ');
        return $this;
    }

    public function where($where, string $op = null, string $val = null, string $type = '', string $andOr = 'AND'): DB {
        if (is_array($where) && !empty($where)) {
            $_where = [];
            foreach ($where as $column => $data) {
                $_where[] = $type . $column . '=' . $this->escape($data);
            }

            $where = implode(' ' . $andOr . ' ', $_where);
        } else {
            if (is_null($where) || empty($where)) {
                return $this;
            } else {
                if (is_array($op)) {
                    $params = explode('?', $where);
                    $_where = '';
                    foreach ($params as $key => $value) {
                        if (! empty($value)) {
                            $_where .= $type . $value . (isset($op[$key]) ? $this->escape($op[$key]) : '');
                        }
                    }
                    $where = $_where;
                } elseif (! in_array($op, self::$op) || $op == false) {
                    $where = $type . $where . ' = ' . $this->escape($op);
                } else {
                    $where = $type . $where . ' ' . $op . ' ' . $this->escape($val);
                }
            }
        }

        if (self::$grouped) {
            $where = '(' . $where;
            self::$grouped = false;
        }
        if (is_null(self::$where)) {
            self::$where = $where;
        } else {
            self::$where = self::$where . ' ' . $andOr . ' ' . $where;
        }

        return $this;
    }

    public function orWhere($where, string $op = null, string $val = null): DB {
        $this->where($where, $op, $val, '', 'OR');
        return $this;
    }

    public function notWhere($where, string $op = null, string $val = null): DB {
        $this->where($where, $op, $val, 'NOT ', 'AND');
        return $this;
    }

    public function orNotWhere($where, string $op = null, string $val = null): DB {
        $this->where($where, $op, $val, 'NOT ', 'OR');
        return $this;
    }

    public function whereNull($where): DB {
        $where = $where . ' IS NULL';
        if (is_null(self::$where)) {
            self::$where = $where;
        } else {
            self::$where = self::$where . ' ' . 'AND ' . $where;
        }

        return $this;
    }

    public function whereNotNull($where): DB {
        $where = $where . ' IS NOT NULL';
        if (is_null(self::$where)) {
            self::$where = $where;
        } else {
            self::$where = self::$where . ' ' . 'AND ' . $where;
        }
        return $this;
    }

    public function grouped(Closure $obj): DB {
        self::$grouped = true;
        call_user_func_array($obj, [$this]);
        self::$where .= ')';
        return $this;
    }

    public function in(string $field, array $keys, string $type = '', string $andOr = 'AND'): DB {
        if (is_array($keys)) {
            $_keys = [];
            foreach ($keys as $k => $v) {
                $_keys[] = (is_numeric($v) ? $v : $this->escape($v));
            }
            $keys = implode(', ', $_keys);
            $where = $field . ' ' . $type . 'IN (' . $keys . ')';
            if (self::$grouped) {
                $where = '(' . $where;
                self::$grouped = false;
            }
            if (is_null(self::$where)) {
                self::$where = $where;
            } else {
                self::$where = self::$where . ' ' . $andOr . ' ' . $where;
            }
        }

        return $this;
    }

    public function notIn(string $field, array $keys): DB {
        $this->in($field, $keys, 'NOT ', 'AND');
        return $this;
    }

    public function orIn(string $field, array $keys): DB {
        $this->in($field, $keys, '', 'OR');
        return $this;
    }

    public function orNotIn(string $field, array $keys): DB {
        $this->in($field, $keys, 'NOT ', 'OR');
        return $this;
    }

    public function between(string $field, string $value1, string $value2, string $type = '', string $andOr = 'AND'): DB {
        $where = '(' . $field . ' ' . $type . 'BETWEEN ' . ($this->escape($value1) . ' AND ' . $this->escape($value2)) . ')';
        if (self::$grouped) {
            $where = '(' . $where;
            self::$grouped = false;
        }

        if (is_null(self::$where)) {
            self::$where = $where;
        } else {
            self::$where = self::$where . ' ' . $andOr . ' ' . $where;
        }

        return $this;
    }

    public function notBetween(string $field, string $value1, string $value2): DB {
        $this->between($field, $value1, $value2, 'NOT ', 'AND');
        return $this;
    }

    public function orBetween(string $field, string $value1, string $value2): DB {
        $this->between($field, $value1, $value2, '', 'OR');
        return $this;
    }

    public function orNotBetween(string $field, string $value1, string $value2): DB {
        $this->between($field, $value1, $value2, 'NOT ', 'OR');
        return $this;
    }

    public function like(string $field, string $data, string $type = '', string $andOr = 'AND'): DB {
        $like = $this->escape($data);
        $where = $field . ' ' . $type . 'LIKE ' . $like;
        if (self::$grouped) {
            $where = '(' . $where;
            self::$grouped = false;
        }

        if (is_null(self::$where)) {
            self::$where = $where;
        } else {
            self::$where = self::$where . ' ' . $andOr . ' ' . $where;
        }

        return $this;
    }

    public function orLike(string $field, string $data): DB {
        $this->like($field, $data, '', 'OR');
        return $this;
    }

    public function notLike(string $field, string $data): DB {
        $this->like($field, $data, 'NOT ', 'AND');
        return $this;
    }

    public function orNotLike(string $field, string $data): DB {
        $this->like($field, $data, 'NOT ', 'OR');
        return $this;
    }

    public function limit(int $limit, int $limitEnd = null): DB {
        if (! is_null($limitEnd)) {
            self::$limit = $limit . ', ' . $limitEnd;
        } else {
            self::$limit = $limit;
        }

        return $this;
    }

    public function offset(int $offset): DB {
        self::$offset = $offset;
        return $this;
    }

    public function pagination(int $perPage, int $page): DB {
        self::$limit = $perPage;
        self::$offset = (($page > 0 ? $page : 1) - 1) * $perPage;
        return $this;
    }

    public function orderBy(string $orderBy, string $orderDir = null): DB {
        if (! is_null($orderDir)) {
            self::$orderBy = $orderBy . ' ' . strtoupper($orderDir);
        } else {
            if (stristr($orderBy, ' ') || $orderBy == 'rand()') {
                self::$orderBy = $orderBy;
            } else {
                self::$orderBy = $orderBy . ' ASC';
            }
        }

        return $this;
    }

    public function groupBy($groupBy) {
        if (is_array($groupBy)) {
            self::$groupBy = implode(', ', $groupBy);
        } else {
            self::$groupBy = $groupBy;
        }

        return $this;
    }

    public function having(string $field, $op = null, string $val = null): DB {
        if (is_array($op)) {
            $fields = explode('?', $field);
            $where = '';
            foreach ($fields as $key => $value) {
                if (! empty($value)) {
                    $where .= $value . (isset($op[$key]) ? self::escape($op[$key]) : '');
                }
            }
            self::$having = $where;
        } elseif (! in_array($op, self::$op)) {
            self::$having = $field . ' > ' . self::escape($op);
        } else {
            self::$having = $field . ' ' . $op . ' ' . self::escape($val);
        }

        return $this;
    }

    public function numRows(): int {
        return self::$numRows;
    }

    public function insertId(): int {
        return self::$insertId;
    }

    public function error() {
        throw new PDOException(self::$error.'('.self::$query.')', 500);
    }

    public function first(string $type = null, string $argument = null) {
        if(self::$type != null && $type == null)
            $type = self::$type;

        if(self::$argument != null && $argument == null)
            $argument = self::$argument;

        self::$limit = 1;
        $query = self::get(true);
        if ($type === true) {
            return $query;
        }

        return self::query($query, false, $type, $argument);
    }

    public function get($type = null, string $argument = null) {
        if(self::$type !== null && $type === null)
            $type = self::$type;

        if(self::$argument !== null && $argument === null)
            $argument = self::$argument;

        $query = 'SELECT ' . self::$select . ' FROM ' . self::$from;
        if (! is_null(self::$join)) {
            $query .= self::$join;
        }

        if (! is_null(self::$where)) {
            $query .= ' WHERE ' . self::$where;
        }

        if (! is_null(self::$groupBy)) {
            $query .= ' GROUP BY ' . self::$groupBy;
        }

        if (! is_null(self::$having)) {
            $query .= ' HAVING ' . self::$having;
        }

        if (! is_null(self::$orderBy)) {
            $query .= ' ORDER BY ' . self::$orderBy;
        }

        if (! is_null(self::$limit)) {
            $query .= ' LIMIT ' . self::$limit;
        }

        if (! is_null(self::$offset)) {
            $query .= ' OFFSET ' . self::$offset;
        }

        if ($type === true) {
            return $query;
        }

        return new Collection(self::query($query, true, $type, $argument));
    }

    public function insert(array $data, bool $type = false) {
        $query = 'INSERT INTO ' . self::$from;
        $values = array_values($data);
        if (isset($values[0]) && is_array($values[0])) {
            $column = implode(', ', array_keys($values[0]));
            $query .= ' (' . $column . ') VALUES ';
            foreach ($values as $value) {
                $val = implode(', ', array_map([$this, 'escape'], $value));
                $query .= '(' . $val . '), ';
            }
            $query = trim($query, ', ');
        } else {
            $column = implode(', ', array_keys($data));
            $val = implode(', ', array_map([$this, 'escape'], $data));
            $query .= ' (' . $column . ') VALUES (' . $val . ')';
        }

        if ($type === true) {
            return $query;
        }

        $query = self::query($query, false);
        if ($query) {
            self::$insertId = self::$pdo->lastInsertId();
            return self::insertId();
        }

        return false;
    }

    public function update(array $data, bool $type = false): int {
        $query = 'UPDATE ' . self::$from . ' SET ';
        $values = [];
        foreach ($data as $column => $val) {
            $values[] = $column . '=' . self::escape($val);
        }

        $query .= implode(',', $values);
        if (! is_null(self::$where)) {
            $query .= ' WHERE ' . self::$where;
        }

        if (! is_null(self::$orderBy)) {
            $query .= ' ORDER BY ' . self::$orderBy;
        }

        if (! is_null(self::$limit)) {
            $query .= ' LIMIT ' . self::$limit;
        }

        if ($type === true) {
            return $query;
        }

        return self::query($query, false);
    }

    public function delete(bool $type = false) {
        $query = 'DELETE FROM ' . self::$from;
        if (! is_null(self::$where)) {
            $query .= ' WHERE ' . self::$where;
        }

        if (! is_null(self::$orderBy)) {
            $query .= ' ORDER BY ' . self::$orderBy;
        }

        if (! is_null(self::$limit)) {
            $query .= ' LIMIT ' . self::$limit;
        }

        if ($query == 'DELETE FROM ' . self::$from) {
            $query = 'TRUNCATE TABLE ' . self::$from;
        }

        if ($type === true) {
            return $query;
        }

        return self::query($query, false);
    }

    public function analyze() {
        return self::query('ANALYZE TABLE ' . self::$from, false);
    }

    public function check() {
        return self::query('CHECK TABLE ' . self::$from, false);
    }

    public function checksum() {
        return self::query('CHECKSUM TABLE ' . self::$from, false);
    }

    public function optimize() {
        return self::query('OPTIMIZE TABLE ' . self::$from, false);
    }

    public function repair() {
        return self::query('REPAIR TABLE ' . self::$from, false);
    }

    public function transaction() {
        if (!self::$transactionCount++) {
            return self::$pdo->beginTransaction();
        }

        self::$pdo->exec('SAVEPOINT trans' . self::$transactionCount);
        return self::$transactionCount >= 0;
    }

    public function commit() {
        if (! --self::$transactionCount) {
            return self::$pdo->commit();
        }

        return self::$transactionCount >= 0;
    }

    public function rollBack() {
        if (--self::$transactionCount) {
            self::$pdo->exec('ROLLBACK TO trans' . self::$transactionCount + 1);
            return true;
        }

        return self::$pdo->rollBack();
    }

    public function exec() {
        if (is_null(self::$query)) {
            return null;
        }

        $query = self::$pdo->exec(self::$query);
        if ($query === false) {
            self::$error = self::$pdo->errorInfo()[2];
            self::error();
        }

        return $query;
    }

    public function fetch(string $type = null, string $argument = null, bool $all = false) {
        if(self::$type !== null && $type === null)
            $type = self::$type;

        if(self::$argument !== null && $argument === null)
            $argument = self::$argument;

        if (is_null(self::$query)) {
            return null;
        }

        $query = self::$pdo->query(self::$query);
        if (! $query) {
            self::$error = self::$pdo->errorInfo()[2];
            self::error();
        }

        $type = self::getFetchType($type);
        if ($type === PDO::FETCH_CLASS) {
            $query->setFetchMode($type, $argument);
        } else {
            $query->setFetchMode($type);
        }

        $result = $all ? $query->fetchAll() : $query->fetch();
        self::$numRows = is_array($result) ? count($result) : 1;
        if($all != false)
            $result = new Collection($result);

        return $result;
    }

    public function fetchAll(string $type = null, string $argument = null) {
        return self::fetch($type, $argument, true);
    }

    public function query(string $query, bool $all = true, string $type = null, string $argument = null) {
        self::reset();
        if (is_array($all) || func_num_args() === 1) {
            $params = explode('?', $query);
            $newQuery = '';
            foreach ($params as $key => $value) {
                if (! empty($value)) {
                    $newQuery .= $value . (isset($all[$key]) ? self::escape($all[$key]) : '');
                }
            }
            self::$query = $newQuery;
            return $this;
        }

        self::$query = preg_replace('/\s\s+|\t\t+/', ' ', trim($query));
        $str = false;
        foreach (['select', 'optimize', 'check', 'repair', 'checksum', 'analyze'] as $value) {
            if (stripos(self::$query, $value) === 0) {
                $str = true;
                break;
            }
        }

        $type = self::getFetchType($type);
        $cache = false;
        if (! is_null(self::$cache) && $type !== PDO::FETCH_CLASS) {
            $cache = self::$cache->getCache(self::$query, $type === PDO::FETCH_ASSOC);
        }

        if (! $cache && $str) {
            $sql = self::$pdo->query(self::$query);
            if ($sql) {
                self::$numRows = $sql->rowCount();
                if ((self::$numRows > 0)) {
                    if ($type === PDO::FETCH_CLASS) {
                        $sql->setFetchMode($type, $argument);
                    } else {
                        $sql->setFetchMode($type);
                    }

                    self::$result = $all ? $sql->fetchAll() : $sql->fetch();
                }

                if (! is_null(self::$cache) && $type !== PDO::FETCH_CLASS) {
                    self::$cache->setCache(self::$query, self::$result);
                }

                self::$cache = null;
            } else {
                self::$cache = null;
                self::$error = self::$pdo->errorInfo()[2];
                self::error();
            }
        } elseif ((! $cache && ! $str) || ($cache && ! $str)) {
            self::$cache = null;
            self::$result = self::$pdo->exec(self::$query);
            if (self::$result === false) {
                self::$error = self::$pdo->errorInfo()[2];
                self::error();
            }
        } else {
            self::$cache = null;
            self::$result = $cache;
            self::$numRows = count(self::$result);
        }

        self::$queryCount++;
        return self::$result;
    }

    public function escape(string $data) {
        if ($data === null) {
            return 'NULL';
        }

        return self::$pdo->quote(trim($data));
    }

    public function cache($time = null) {
        if($time === null) {
            $time = self::$cacheTime;
        }

        self::$cache = new Cache(self::$cacheDir, $time);
        return $this;
    }

    public static function queryCount(): int {
        return self::$queryCount;
    }

    public function getQuery(): string {
        return self::$query;
    }

    public function __destruct() {
        self::$pdo = null;
    }

    protected function reset(): void {
        self::$select = '*';
        self::$from = null;
        self::$where = null;
        self::$limit = null;
        self::$offset = null;
        self::$orderBy = null;
        self::$groupBy = null;
        self::$having = null;
        self::$join = null;
        self::$grouped = false;
        self::$numRows = 0;
        self::$insertId = null;
        self::$query = null;
        self::$error = null;
        self::$result = [];
        self::$transactionCount = 0;
    }

    protected function getFetchType($type) {
        return $type === 'class'
            ? PDO::FETCH_CLASS
            : ($type === 'array'
                ? PDO::FETCH_ASSOC
                : PDO::FETCH_OBJ);
    }

}