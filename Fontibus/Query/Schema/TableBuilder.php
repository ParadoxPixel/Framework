<?php
namespace Fontibus\Query\Schema;

use Fontibus\Query\DB;
use Fontibus\Query\Processor;

class TableBuilder {

    private Processor $processor;
    private string $table;
    private array $columns = [];
    private array $unique = [];

    public function __construct(Processor $processor, string $table) {
        $this->processor = $processor;
        $this->table = $table;
    }

    /**
     * Add column to Table Builder
     * @param string $column
     * @param string $type
     * @param int $length
     * @return ColumnBuilder|string
     */
    public function setColumn(string $column, string $type = 'VARCHAR', int $length = 0) {
        $column = new ColumnBuilder($column, $type, $length);
        array_push($this->columns, $column);
        return $column;
    }

    /**
     * Column of type Varchar
     * @param string $column
     * @param int $length
     * @return ColumnBuilder|string
     */
    public function string(string $column, int $length = 255) {
        return $this->setColumn($column, 'VARCHAR', $length);
    }

    /**
     * Column of type Tiny Text
     * @param string $column
     * @return ColumnBuilder|string
     */
    public function tinyText(string $column) {
        return $this->setColumn($column, 'TINYTEXT');
    }

    /**
     * Column of type Text
     * @param string $column
     * @return ColumnBuilder|string
     */
    public function text(string $column) {
        return $this->setColumn($column, 'TEXT');
    }

    /**
     * Column of type Medium Text
     * @param string $column
     * @return ColumnBuilder|string
     */
    public function mediumText(string $column) {
        return $this->setColumn($column, 'MEDIUMTEXT');
    }

    /**
     * Column of type Long Text
     * @param string $column
     * @return ColumnBuilder|string
     */
    public function longText(string $column) {
        return $this->setColumn($column, 'LONGTEXT');
    }

    /**
     * Column of type Tiny Integer
     * @param string $column
     * @return ColumnBuilder|string
     */
    public function tinyInt(string $column) {
        return $this->setColumn($column, 'TINYINT');
    }

    /**
     * Column of type Integer
     * @param string $column
     * @return ColumnBuilder|string
     */
    public function int(string $column) {
        return $this->setColumn($column, 'INT');
    }

    /**
     * Column of type Medium Integer
     * @param string $column
     * @return ColumnBuilder|string
     */
    public function mediumInt(string $column): ColumnBuilder {
        return $this->setColumn($column, 'MEDIUMINT');
    }

    /**
     * Column of type Big Integer
     * @param string $column
     * @return ColumnBuilder|string
     */
    public function bigInt(string $column): ColumnBuilder {
        return $this->setColumn($column, 'BIGINT');
    }

    /**
     * Column of type Boolean
     * @param string $column
     * @return ColumnBuilder|string
     */
    public function bool(string $column): ColumnBuilder {
        return $this->setColumn($column, 'BOOLEAN');
    }

    /**
     * Column of type Date
     * @param string $column
     * @return ColumnBuilder|string
     */
    public function date(string $column): ColumnBuilder {
        return $this->setColumn($column, 'DATE');
    }

    /**
     * Column of type Timestamp
     * @param string $column
     * @return ColumnBuilder|string
     */
    public function timestamp(string $column): ColumnBuilder {
        return $this->setColumn($column, 'TIMESTAMP');
    }

    /**
     * Columns updated_at and created_at
     */
    public function timestamps() {
        $this->timestamp('updated_at');
        $this->timestamp('created_at');
    }

    /**
     * Column of type Big Integer, Primary Key and Auto Increments
     * @param string $column
     * @return ColumnBuilder|string
     */
    public function bigIncrements(string $column) {
        $column = $this->bigInt($column);
        $column->primaryKey()->autoIncrements();
        return $column;
    }

    /**
     * Unique Column(s)
     * @param $value
     */
    public function unique($value) {
        if(is_array($value)) {
            array_push($this->unique, $value);
            return;
        }

        array_push($this->unique, [$value]);
    }

    /**
     * Build SQL Query
     * @return string
     */
    public function toSQL() {
        $str = 'CREATE TABLE IF NOT EXISTS '.$this->table.'(';
        $columns = '';
        foreach($this->columns as $column) {
            if(!empty($columns))
                $columns .= ', ';

            $columns .= $column->toSQL();
        }

        $str .= $columns;
        $unique = '';
        foreach($this->unique as $row)
            $unique .= ', UNIQUE('.implode(', ', DB::quoteColumn($row)).')';

        $str .= $unique.') ENGINE = InnoDB;';
        return $str;
    }

    /**
     * Create Table
     */
    public function create() {
        $sql = $this->toSQL();
        $this->processor->query($sql);
    }

}