<?php
namespace Fontibus\Facades\ValidationRules;

class PasswordRule extends Rule {

    /**
     * Check if password has valid format
     * @param string $argument
     * @param string $value
     * @return bool
     */
    public function check(string $argument, string $value): bool {
       return preg_match('/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%?]).*$/', $value);
    }

    /**
     * Return error message
     * @return string
     */
    public function getMessage(): string {
        return ':field is an invalid password format';
    }

}