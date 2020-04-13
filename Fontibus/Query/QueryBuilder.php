<?php
namespace Fontibus\Query;

use Closure;
use Fontibus\Collection\ArrayUtil;
use Fontibus\Collection\Collection;
use Fontibus\Query\Clauses\JoinClause;
use Fontibus\Query\Clauses\WhereClause;
use PDO;

class QueryBuilder {

    private Processor $processor;
    private string $table, $queryTemplate;

    private array $select = [];
    private array $update = [];
    private array $insert = [];

    private array $where = [];
    private array $join = [];
    private array $groupBy = [];
    private string $orderBy = '';
    private int $limit = 0, $offset = 0;

    private int $fetch_mode = PDO::FETCH_ASSOC;
    private string $fetch_argument = '';

    public function __construct(Processor $processor, string $table) {
        $this->processor = $processor;
        $this->table = $table;
        $this->queryTemplate = Templates::$select;
    }

    public function select($value) {
        if(is_array($value)) {
            foreach ($value as $column)
                array_push($this->select,($column instanceof RawData) ? $column->getValue() : DB::quoteColumn($column));
        } else {
            array_push($this->select, ($value instanceof RawData) ? $value->getValue() : DB::quoteColumn($value));
        }

        return $this;
    }

    public function update(array $values) {
        $this->queryTemplate = Templates::$update;
        foreach ($values as $row)
            array_push($this->update, ($row instanceof RawData) ? $row->getValue() : $row);

        $sql = $this->toSQL();
        $stmt = $this->processor->query($sql);
        $rows = $stmt->rowCount();
        unset($stmt);
        return $rows;
    }

    public function delete() {
        $this->queryTemplate = Templates::$delete;
        $sql = $this->toSQL();

        $stmt = $this->processor->query($sql);
        $rows = $stmt->rowCount();
        unset($stmt);
        return $rows;
    }

    public function insert(array $value) {
        if(ArrayUtil::isMulti($value)) {
            $keys = [];
            $values = [];
            foreach ($value as $row) {
                if(empty($keys))
                    $keys = ArrayUtil::getKeys($row);

                array_push($values, ArrayUtil::getValues($row));
            }

            $this->insert = [
                'keys' => $keys,
                'values' => $values
            ];
        } else {
            $this->insert = ArrayUtil::splitArray($value);
        }

        $this->queryTemplate = Templates::$insert;
        $sql = $this->toSQL();

        $stmt = $this->processor->query($sql);
        $rows = $stmt->rowCount();
        unset($stmt);
        return $rows;
    }

    public function get($all = true) {
        $sql = $this->toSQL();
        $stmt = $this->processor->query($sql, []);
        !empty($this->fetch_argument) ? $stmt->setFetchMode($this->fetch_mode, $this->fetch_argument) : $stmt->setFetchMode($this->fetch_mode);
        $result = ($all ? $stmt->fetchAll() : $stmt->fetch());
        unset($stmt);

        if($this->fetch_mode != PDO::FETCH_CLASS) {
            if (is_array($result)) {
                $result = new Collection($result);
            } else if (is_object($result)) {
                $result = new Collection((array)$result);
            }
        } else if($result === false)
            return null;

        return $result;
    }

    public function first() {
        return $this->get(false);
    }

    public function count(string $column, string $name = null) {
        return $this->select(DB::raw('COUNT('.DB::quoteColumn($column).')'.(empty($name) ? $name : ' AS '.DB::quoteColumn($name))));
    }

    public function where($first, $operator = null, $second = null, $type = 'and') {
        if($first instanceof Closure) {
            return $this->whereNested($this->where, $first, $type);
        }

        return $this->whereColumn($this->where, $first, $operator, $second, $type);
    }

    public function orWhere($first, $operator = null, $second = null) {
        return $this->where($first, $operator, $second, 'or');
    }

    public function whereNested(array &$array, $first, $type) {
        $clause = new WhereClause($this, $type);
        $first($clause);
        array_push($array, [
            'clause' => '('.$clause->buildClause().')',
            'type' => $type
        ]);
        return $this;
    }

    public function whereColumn(array &$array, $first, $operator = null, $second = null, $type = 'and') {
        if($first instanceof RawData) {
            $clause = DB::sanitize($first->getValue());
        } else {
            $clause = DB::quoteColumn($first).$operator.DB::quote($second);
        }

        array_push($array, [
            'clause' => $clause,
            'type' => strtoupper($type)
        ]);
        return $this;
    }

    public function buildWhere() {
        $str = '';
        foreach($this->where as $row) {
            if(!empty($str))
                $str .= ' '.$row['type'].' ';

            $str .= $row['clause'];
        }

        return $str;
    }

    public function join($table, $first, $operator = null, $second = null, $type = 'inner', $where = 'on') {
        if($first instanceof Closure) {
            $clause = new JoinClause($this, $type, $table);

            $first($clause);

            array_push($this->join, [
                'table' => DB::quoteColumn($table),
                'type' => strtoupper($type),
                'where' => $where,
                'clause' => $clause->buildClause()
            ]);
        } else {
            array_push($this->join, [
                'table' => DB::quoteColumn($table),
                'type' => strtoupper($type),
                'where' => $where,
                'clause' => DB::quoteColumn($first).$operator.DB::quote($second)
            ]);
        }

        return $this;
    }

    public function leftJoin($table, $first, $operator = null, $second = null, $where = 'on') {
        return $this->join($table, $first, $operator, $second, 'left', $where);
    }

    public function rightJoin($table, $first, $operator = null, $second = null, $where = 'on') {
        return $this->join($table, $first, $operator, $second, 'right', $where);
    }

    public function leftOuterJoin($table, $first, $operator = null, $second = null, $where = 'on') {
        return $this->join($table, $first, $operator, $second, 'left outer', $where);
    }

    public function rightOuterJoin($table, $first, $operator = null, $second = null, $where = 'on') {
        return $this->join($table, $first, $operator, $second, 'right outer', $where);
    }

    public function groupBy($first) {
        if(is_array($first)) {
            foreach ($first as $value)
                array_push($this->groupBy, ($value instanceof RawData) ? $value->getValue() : $value);
        } else {
            array_push($this->groupBy, $first);
        }

        return $this;
    }

    public function orderBy(string $field, string $order = 'asc') {
        $this->orderBy = $field.' '.strtoupper($order);
        return $this;
    }

    public function orderByDesc(string $field) {
        return $this->orderBy($field, 'desc');
    }

    public function skip(int $i) {
        if($i < 1)
            return $this;

        $this->offset = $i;
        return $this;
    }

    public function take(int $i) {
        if($i < 1)
            return $this;

        $this->limit = $i;
        return $this;
    }

    public function template(string $template) {
        $this->queryTemplate = $template;
        return $this;
    }

    public function setFetchMode(int $mode, string $argument = null) {
        $this->fetch_mode = $mode;
        $this->fetch_argument = $argument;
        return $this;
    }

    public function toSQL() {
        $template = $this->queryTemplate;
        $template = str_replace('%TABLE%', DB::quoteColumn($this->table), $template);

        preg_match_all('/\%(.*?)\%/', $template, $matches, PREG_SET_ORDER);
        foreach($matches as $entry) {
            $value = strtolower($entry[1]);
            $value = explode('_', $value);
            for($i = 0; $i < count($value); $i++)
                $value[$i] = ucfirst($value[$i]);

            $value = 'get'.implode('', $value);
            $value = $this->$value();
            $template = str_replace($entry[0], $value, $template);
        }

        return $template;
    }

    public function getSelectFields() {
        if(empty($this->select))
            return '*';

        return implode(', ', $this->select);
    }

    public function getInsertFields() {
        return implode(', ', DB::quoteColumn($this->insert['keys']));
    }

    public function getInsertValues() {
        $str = '';
        foreach($this->insert['values'] as $row) {
            if(!empty($str))
                $str .= ', ';

            $str .= '('.implode(',', DB::quote($row)).')';
        }

        return $str;
    }

    public function getUpdateFields() {
        return implode(', ', $this->update);
    }

    public function getJoin() {
        $join = '';
        foreach($this->join as $row) {
            if(!empty($join))
                $join .= ' ';

            $join .= $row['type'].' JOIN '.$row['table'].' '.$row['where'].' '.$row['clause'];
        }

        return $join;
    }

    public function getWhere() {
        $where = $this->buildWhere();
        if(!empty($where))
            $where = 'WHERE '.$where;

        return $where;
    }

    public function getGroupBy() {
        $groupBy = '';
        if(!empty($this->groupBy))
            $groupBy = 'GROUP BY '.implode(', ', $this->groupBy);

        return $groupBy;
    }

    public function getOrderBy() {
        $orderBy = '';
        if(!empty($this->orderBy))
            $orderBy = 'ORDER BY '.$this->orderBy;

        return $orderBy;
    }

    public function getLimit() {
        $limit = '';
        if($this->limit > 0)
            $limit = 'LIMIT '.$this->limit;

        return $limit;
    }

    public function getOffset() {
        $offset = '';
        if($this->offset > 0)
            $offset = 'OFFSET '.$this->offset;

        return $offset;
    }

}