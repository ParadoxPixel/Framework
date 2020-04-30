<?php
namespace Fontibus\Facades\ValidationRules;

class EmailRule extends Rule {

    /**
     * Check if valid email
     * @param string $argument
     * @param string $value
     * @return bool
     */
    public function check(string $argument, string $value): bool {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Return error message
     * @return string
     */
    public function getMessage(): string {
        return ':field with value: :value is an invalid email';
    }

}