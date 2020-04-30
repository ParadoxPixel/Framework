<?php
namespace Fontibus\Facades\ValidationRules;

abstract class Rule {

    public abstract function check(string $argument, string $value): bool;

    public abstract function getMessage(): string;

}