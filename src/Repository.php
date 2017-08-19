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
        $stmt = $this->prepare($sql, $params);

        $stmt->execute($params);

        return $stmt->rowCount();
    }

    public function queryAll($sql, array $params = [], string $fetch_class = '')
    {
        $smtp = $this->prepare($sql, $params);

        $smtp->execute($params);

        if (!empty($fetch_class)) {
            return $smtp->fetchAll(\PDO::FETCH_CLASS, $fetch_class);
        }

        return $smtp->fetchAll(\PDO::FETCH_ASSOC);
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

        return $stmt;
    }
}