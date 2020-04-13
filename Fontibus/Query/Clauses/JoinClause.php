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

    public function on($first, $operator = null, $second = null, $type = 'and') {
        if($first instanceof Closure)
            return $this->whereNested($this->on, $first, $type);

        return $this->whereColumn($this->on, $first, $operator, $second, $type);
    }

    public function orOn($first, $operator = null, $second = null) {
        return $this->on($first, $operator, $second, 'or');
    }

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