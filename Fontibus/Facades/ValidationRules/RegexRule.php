<?php
namespace Fontibus\Facades\ValidationRules;

class RegexRule extends Rule {

    /**
     * Check if regex matches
     * @param string $argument
     * @param string $value
     * @return bool
     */
    public function check(string $argument, string $value): bool {
        return preg_match($argument, $value);
    }

    /**
     * Return error message
     * @return string
     */
    public function getMessage(): string {
        return ':field doesn\'t match regex';
    }

}