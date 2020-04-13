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

    public function query(string $query, array $vars = []): PDOStatement {
        $stmt = $this->pdo->prepare($query);
        foreach ($vars as $key => $value)
            $stmt->bindParam($key, $value);

        $stmt->execute();
        return $stmt;
    }

    public function quote(string $input) {
        return $this->pdo->quote($input);
    }

}