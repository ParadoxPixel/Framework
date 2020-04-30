<?php
namespace Fontibus\Query;

class RawData {

    private string $value;

    public function __construct(string $value) {
        $this->value = $value;
    }

    /**
     * Get raw value
     * @return string
     */
    public function getValue(): string {
        return $this->value;
    }

}