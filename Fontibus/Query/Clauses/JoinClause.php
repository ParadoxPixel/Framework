<?php
namespace Fontibus\Query\Clauses;

use Closure;
use Fontibus\Query\QueryBuilder;

class JoinClause extends QueryBuilder {

    private QueryBuilder $instance;
    private string $type, $table;

    private array $on = [];

    public function __construct(QueryBuilder $instance, string $type, string $table) {
        $this->instance = $instance;
        $this->type = $type;
        $this->table = $table;
    }

    /**
     * Build ON
     * @param $first
     * @param null $operator
     * @param null $second
     * @param string $type
     * @return JoinClause
     */
    public function on($first, $operator = null, $second = null, $type = 'and') {
        if($first instanceof Closure)
            return $this->whereNested($this->on, $first, $type);

        return $this->whereColumn($this->on, $first, $operator, $second, $type);
    }

    /**
     * Build OR ON
     * @param $first
     * @param null $operator
     * @param null $second
     * @return JoinClause
     */
    public function orOn($first, $operator = null, $second = null) {
        return $this->on($first, $operator, $second, 'or');
    }

    /**
     * Build Join Clause
     * @return string
     */
    public function buildClause() {
        $str = '';
        foreach($this->on as $row) {
            if(!empty($str))
                $str .= ' '.$row['type'].' ';

            $str .= $row['clause'];
        }

        $where = $this->buildWhere();
        if(!empty($str) && !empty($where))
            $str .= ' WHERE ';

        $str .= $where;
        return $str;
    }

}