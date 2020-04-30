<?php
namespace Fontibus\Query\Clauses;

use Closure;
use Fontibus\Query\QueryBuilder;

class WhereClause extends QueryBuilder {

    private QueryBuilder $instance;
    private string $type;

    private array $where = [];

    public function __construct(QueryBuilder $instance, string $type) {
        $this->instance = $instance;
        $this->type = $type;
    }

    /**
     * Where Clause
     * @param $first
     * @param null $operator
     * @param null $second
     * @param string $type
     * @return WhereClause|QueryBuilder
     */
    public function where($first, $operator = null, $second = null, $type = 'and') {
        if($first instanceof Closure)
            return $this->whereNested($this->where, $first, $type);

        return $this->whereColumn($this->where, $first, $operator, $second, $type);
    }

    /**
     * Or Where Clause
     * @param $first
     * @param null $operator
     * @param null $second
     * @return WhereClause|QueryBuilder
     */
    public function orWhere($first, $operator = null, $second = null) {
        return $this->where($first, $operator, $second, 'or');
    }

    /**
     * Build Nested Where Clause
     * @return string
     */
    public function buildClause() {
        $str = '';
        foreach($this->where as $row) {
            if(!empty($str))
                $str .= ' '.$row['type'].' ';

            $str .= $row['clause'];
        }

        return $str;
    }

}