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

    /**
     * Specify select columns
     * @param $value
     * @return $this
     */
    public function select($value) {
        if(is_array($value)) {
            foreach ($value as $column)
                array_push($this->select,($column instanceof RawData) ? $column->getValue() : DB::quoteColumn($column));
        } else {
            array_push($this->select, ($value instanceof RawData) ? $value->getValue() : DB::quoteColumn($value));
        }

        return $this;
    }

    /**
     * Pass values to be updated
     * @param array $values
     * @return mixed
     */
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

    /**
     * Delete data from table
     * @return mixed
     */
    public function delete() {
        $this->queryTemplate = Templates::$delete;
        $sql = $this->toSQL();

        $stmt = $this->processor->query($sql);
        $rows = $stmt->rowCount();
        unset($stmt);
        return $rows;
    }

    /**
     * Insert data into table
     * @param array $value
     * @return mixed
     */
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

    /**
     * Perform select query and get data
     * @param bool $all
     * @return Collection|null
     */
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

    /**
     * Return only first result
     * @return Collection|null
     */
    public function first() {
        return $this->get(false);
    }

    /**
     * Count column
     * @param string $column
     * @param string|null $name
     * @return $this
     */
    public function count(string $column, string $name = null) {
        return $this->select(DB::raw('COUNT('.DB::quoteColumn($column).')'.(empty($name) ? $name : ' AS '.DB::quoteColumn($name))));
    }

    /**
     * Where Clause
     * @param $first
     * @param null $operator
     * @param null $second
     * @param string $type
     * @return $this
     */
    public function where($first, $operator = null, $second = null, $type = 'and') {
        if($first instanceof Closure) {
            return $this->whereNested($this->where, $first, $type);
        }

        return $this->whereColumn($this->where, $first, $operator, $second, $type);
    }

    /**
     * Or Where Clause
     * @param $first
     * @param null $operator
     * @param null $second
     * @return $this
     */
    public function orWhere($first, $operator = null, $second = null) {
        return $this->where($first, $operator, $second, 'or');
    }

    /**
     * Handle Nested Where Clause
     * @param array $array
     * @param $first
     * @param $type
     * @return $this
     */
    public function whereNested(array &$array, $first, $type) {
        $clause = new WhereClause($this, $type);
        $first($clause);
        array_push($array, [
            'clause' => '('.$clause->buildClause().')',
            'type' => $type
        ]);
        return $this;
    }

    /**
     * Handle Where Clause
     * @param array $array
     * @param $first
     * @param null $operator
     * @param null $second
     * @param string $type
     * @return $this
     */
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

    /**
     * Build Where Clause
     * @return string
     */
    public function buildWhere() {
        $str = '';
        foreach($this->where as $row) {
            if(!empty($str))
                $str .= ' '.$row['type'].' ';

            $str .= $row['clause'];
        }

        return $str;
    }

    /**
     * Join Clause
     * @param $table
     * @param $first
     * @param null $operator
     * @param null $second
     * @param string $type
     * @param string $where
     * @return $this
     */
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

    /**
     * left Join Clause
     * @param $table
     * @param $first
     * @param null $operator
     * @param null $second
     * @param string $where
     * @return $this
     */
    public function leftJoin($table, $first, $operator = null, $second = null, $where = 'on') {
        return $this->join($table, $first, $operator, $second, 'left', $where);
    }

    /**
     * Right Join Clause
     * @param $table
     * @param $first
     * @param null $operator
     * @param null $second
     * @param string $where
     * @return $this
     */
    public function rightJoin($table, $first, $operator = null, $second = null, $where = 'on') {
        return $this->join($table, $first, $operator, $second, 'right', $where);
    }

    /**
     * Left Outer Join Clause
     * @param $table
     * @param $first
     * @param null $operator
     * @param null $second
     * @param string $where
     * @return $this
     */
    public function leftOuterJoin($table, $first, $operator = null, $second = null, $where = 'on') {
        return $this->join($table, $first, $operator, $second, 'left outer', $where);
    }

    /**
     * Right Outer Join Clause
     * @param $table
     * @param $first
     * @param null $operator
     * @param null $second
     * @param string $where
     * @return $this
     */
    public function rightOuterJoin($table, $first, $operator = null, $second = null, $where = 'on') {
        return $this->join($table, $first, $operator, $second, 'right outer', $where);
    }

    /**
     * Prepare Group By
     * @param $first
     * @return $this
     */
    public function groupBy($first) {
        if(is_array($first)) {
            foreach ($first as $value)
                array_push($this->groupBy, ($value instanceof RawData) ? $value->getValue() : $value);
        } else {
            array_push($this->groupBy, $first);
        }

        return $this;
    }

    /**
     * Prepare Order By
     * @param string $field
     * @param string $order
     * @return $this
     */
    public function orderBy(string $field, string $order = 'asc') {
        $this->orderBy = $field.' '.strtoupper($order);
        return $this;
    }

    /**
     * Prepare Order By Descending
     * @param string $field
     * @return $this
     */
    public function orderByDesc(string $field) {
        return $this->orderBy($field, 'desc');
    }

    /**
     * Skip rows
     * @param int $i
     * @return $this
     */
    public function skip(int $i) {
        if($i < 1)
            return $this;

        $this->offset = $i;
        return $this;
    }

    /**
     * Limit rows in result
     * @param int $i
     * @return $this
     */
    public function take(int $i) {
        if($i < 1)
            return $this;

        $this->limit = $i;
        return $this;
    }

    /**
     * Set custom template for query
     * @param string $template
     * @return $this
     */
    public function template(string $template) {
        $this->queryTemplate = $template;
        return $this;
    }

    /**
     * Set fetch mode
     * @param int $mode
     * @param string|null $argument
     * @return $this
     */
    public function setFetchMode(int $mode, string $argument = null) {
        $this->fetch_mode = $mode;
        $this->fetch_argument = $argument;
        return $this;
    }

    /**
     * Build Query
     * @return string|string[]
     */
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

    /**
     * Build Select Fields
     * @return string
     */
    public function getSelectFields() {
        if(empty($this->select))
            return '*';

        return implode(', ', $this->select);
    }

    /**
     * Build Insert Fields
     * @return string
     */
    public function getInsertFields() {
        return implode(', ', DB::quoteColumn($this->insert['keys']));
    }

    /**
     * Build Insert Values
     * @return string
     */
    public function getInsertValues() {
        $str = '';
        foreach($this->insert['values'] as $row) {
            if(!empty($str))
                $str .= ', ';

            $str .= '('.implode(',', DB::quote($row)).')';
        }

        return $str;
    }

    /**
     * Build Update Fields
     * @return string
     */
    public function getUpdateFields() {
        return implode(', ', $this->update);
    }

    /**
     * Build Join
     * @return string
     */
    public function getJoin() {
        $join = '';
        foreach($this->join as $row) {
            if(!empty($join))
                $join .= ' ';

            $join .= $row['type'].' JOIN '.$row['table'].' '.$row['where'].' '.$row['clause'];
        }

        return $join;
    }

    /**
     * Build Where
     * @return string
     */
    public function getWhere() {
        $where = $this->buildWhere();
        if(!empty($where))
            $where = 'WHERE '.$where;

        return $where;
    }

    /**
     * Build Group By
     * @return string
     */
    public function getGroupBy() {
        $groupBy = '';
        if(!empty($this->groupBy))
            $groupBy = 'GROUP BY '.implode(', ', $this->groupBy);

        return $groupBy;
    }

    /**
     * Build Order By
     * @return string
     */
    public function getOrderBy() {
        $orderBy = '';
        if(!empty($this->orderBy))
            $orderBy = 'ORDER BY '.$this->orderBy;

        return $orderBy;
    }

    /**
     * Build Limit
     * @return string
     */
    public function getLimit() {
        $limit = '';
        if($this->limit > 0)
            $limit = 'LIMIT '.$this->limit;

        return $limit;
    }

    /**
     * Build Offset
     * @return string
     */
    public function getOffset() {
        $offset = '';
        if($this->offset > 0)
            $offset = 'OFFSET '.$this->offset;

        return $offset;
    }

}