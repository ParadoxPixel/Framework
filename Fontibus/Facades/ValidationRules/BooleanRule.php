<?php
namespace Fontibus\Facades\ValidationRules;

class BooleanRule extends Rule {

    /**
     * Check if boolean
     * @param string $argument
     * @param string $value
     * @return bool
     */
    public function check(string $argument, string $value): bool {
       return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Return error message
     * @return string
     */
    public function getMessage(): string {
        return ':field is an invalid boolean';
    }

}