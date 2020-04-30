<?php
namespace Fontibus\Query\Schema;

use Fontibus\Query\DB;

class ColumnBuilder {

    private string $column;
    private string $type;
    private int $length;
    private bool $null = false;
    private bool $primary = false;
    private string $default = '';
    private bool $autoIncrements = false;

    public function __construct(string $column, string $type, int $length) {
        $this->column = $column;
        $this->type = $type;
        $this->length = $length;
    }

    /**
     * Make Primary Key
     * @return $this
     */
    public function primaryKey() {
        $this->primary = true;
        return $this;
    }

    /**
     * Make AUTO_INCREMENT
     * @return $this
     */
    public function autoIncrements() {
        $this->autoIncrements = true;
        return $this;
    }

    /**
     * Is nullable
     * @return $this
     */
    public function nullable() {
        $this->null = true;
        return $this;
    }

    /**
     * Set default value
     * @param string $value
     */
    public function default(string $value) {
        $this->default = $value;
    }

    /**
     * Build SQL
     * @return string
     */
    public function toSQL() {
        $str = DB::quoteColumn($this->column).' '.$this->type;
        if($this->length > 0)
            $str .= '('.$this->length.')';

        if(!$this->null)
            $str .= ' NOT NULL';

        if($this->default != '') {
            $str .= ' DEFAULT ' . DB::quote($this->default);
        }

        if($this->autoIncrements && $this->primary)
            $str .= ' AUTO_INCREMENT';

        if($this->primary)
            $str .=', PRIMARY KEY('.DB::quoteColumn($this->column).')';

        return $str;
    }

}