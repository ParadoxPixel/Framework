<?php
namespace Fontibus\Query\Schema;

use Closure;
use Fontibus\Query\Processor;

class Schema {

    private static Processor $processor;

    public function __construct(Processor $processor) {
        self::$processor = $processor;
    }

    /**
     * Build Schema
     * @param string $table
     * @param Closure $build
     * @return TableBuilder
     */
    public function build(string $table, Closure $build) {
        $builder = new TableBuilder(self::$processor, $table);
        $build($builder);
        return $builder;
    }

}