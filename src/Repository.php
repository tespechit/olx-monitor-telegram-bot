<?php

namespace App;


class Repository
{
    /**
     * @var \PDO
     */
    protected $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function exec(string $sql, array $params = [])
    {
        return $this->prepare($sql, $params)
            ->rowCount();
    }

    public function queryAll($sql, array $params = [], string $fetch_class = '')
    {
        $fetch_style = empty($fetch_class) ? \PDO::FETCH_ASSOC: \PDO::FETCH_CLASS;

        return $this->prepare($sql, $params)
            ->fetchAll($fetch_style, $fetch_class);
    }

    private function prepare(string $sql, array $params = [])
    {
        if (!$stmt = $this->pdo->prepare($sql)) {
            $message = implode(' - ', $this->pdo->errorInfo());
            throw new \PDOException($message);
        }

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return $stmt;
    }
}