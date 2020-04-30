<?php
namespace Fontibus\Facades;

use Fontibus\Facades\ValidationRules\Rule;

class Validator {

    private bool $passed = false;
    private array $messages = [];
    private array $errors = [];

    public function __construct(array $data, array $settings, array $messages = []) {
        foreach($settings as $key => $value) {
            if(empty($value) || array_key_exists($key, $data))
                continue;

            if(!is_array($value))
                $value = explode($value, '||');

            $this->handleRule($key, $data[$key], $value);
        }

        $this->passed = true;
        $this->messages = $messages;
    }

    /**
     * Check if data was correct
     * @return bool
     */
    public function passed(): bool {
        return $this->passed;
    }

    /**
     * Handle rule for field, value
     * @param string $field
     * @param string $value
     * @param string $rule
     */
    public function handleRule(string $field, string $value, string $rule): void {
        $args = explode(':', $rule);
        if(count($args) < 2)
            $args[1] = '';

        $rule = self::getRule($args[0]);
        if(empty($rule))
            return;

        $check = $rule->check($args[1], $value);
        if(!$check) {
            $this->passed = false;

            $message = array_key_exists($field, $this->messages) ? $this->messages[$field] : $rule->getMessage();
            $message = str_replace(':field', $field, $message);
            $message = str_replace(':value', $value, $message);
            $this->errors[$field] = $message;
        }
    }

    /**
     * Return instance of Rule class by name
     * @param string $name
     * @return Rule
     */
    public static function getRule(string $name): Rule {
        if(class_exists($name)) {
            $class = new $name();
            if(is_subclass_of($class, 'Fontibus\Facades\ValidationRules\Rule'))
                return $class;
        }

        $name = ucfirst($name).'Rule';
        $rule = 'Fontibus\\Facades\\ValidationRules\\'.$name;
        if(!class_exists($rule))
            return null;

        return new $rule();
    }

}