<?php
namespace Fontibus\Facades\ValidationRules;

use Fontibus\Query\DB;

class ExistsRule extends Rule {

    /**
     * Check if value exists in database
     * @param string $argument
     * @param string $value
     * @return bool
     */
    public function check(string $argument, string $value): bool {
        $args = explode(',', $argument);
        $length = count($args);
        if($length < 2)
            return false;

        $column = $length < 3 ? 'id' : $args[2];
        return DB::table($args[0])->where($args[1], '=', $value)->count($column) > 0;
    }

    /**
     * Return error message
     * @return string
     */
    public function getMessage(): string {
        return 'Value: :value for :field doesn\'t exist';
    }

}