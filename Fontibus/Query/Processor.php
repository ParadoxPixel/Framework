<?php
namespace Fontibus\Query;

use PDO;
use PDOStatement;

class Processor {

    private PDO $pdo;

    public function __construct(array $settings) {
        if(!isset($settings['charset']))
            $settings['charset'] = 'utf8';

        $pdo = new PDO('mysql:host=' . $settings['host'] . ';port=' . $settings['port'] . ';dbname=' . $settings['name'] . ';charset=' . $settings['charset'], $settings['user'], $settings['pass']);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        $this->pdo = $pdo;
    }

    /**
     * Perform Query
     * @param string $query
     * @param array $vars
     * @return PDOStatement
     */
    public function query(string $query, array $vars = []): PDOStatement {
        $stmt = $this->pdo->prepare($query);
        foreach ($vars as $key => $value)
            $stmt->bindParam($key, $value);

        $stmt->execute();
        return $stmt;
    }

    /**
     * Quote Input
     * @param string $input
     * @return false|string
     */
    public function quote(string $input) {
        return $this->pdo->quote($input);
    }

}