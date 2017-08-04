<?php

namespace App;

use SQLite3;

class AnunciosRepository
{
    protected $sqlite;

    public function __construct($db_file)
    {
        $this->sqlite = new SQLite3($db_file);
    }

    public function all()
    {
        $sql = 'SELECT id, titulo, url, preco, quartos, area, carros, cidade, bairro, cep, foto, created_at FROM anuncios';
        return $this->query($sql);
    }

    public function byId($ids)
    {
        $sql = "SELECT id, titulo, url, preco, quartos, area, carros, cidade, bairro, cep, foto, created_at 
                FROM anuncios
                WHERE id IN ('" . implode("','", $ids) . "')";

        return $this->queryAll($sql);
    }

    public static function criarSchema($db_file)
    {
        $sqlite = new SQLite3($db_file);

        $sqlite->exec('PRAGMA encoding="UTF-8";');

        $sql = 'CREATE TABLE anuncios
                (
                    id INT,
                    titulo TEXT,
                    url TEXT,
                    preco INT,
                    quartos INT,
                    area INT,
                    carros INT,
                    cidade TEXT,
                    bairro TEXT,
                    cep INT,
                    foto TEXT,
                    created_at DATETIME
                )';

        $sqlite->exec($sql);
    }

    public function save($anuncios)
    {
        $values = [];

        foreach ($anuncios as $anuncio) {

            $values[] = "(
            '{$anuncio['id']}', '{$anuncio['titulo']}', '{$anuncio['url']}', '{$anuncio['preco']}', '{$anuncio['quartos']}',
            '{$anuncio['area']}', '{$anuncio['carros']}', '{$anuncio['cidade']}', '{$anuncio['bairro']}', '{$anuncio['cep']}', 
            '{$anuncio['foto']}', '{$anuncio['created_at']}'
            )";
        }

        $sql = "INSERT INTO anuncios (
                  id, titulo, url, preco, quartos,
                  area, carros, cidade, bairro, cep,
                  foto, created_at
                ) VALUES " . implode(',', $values);

        return $this->exec($sql);
    }

    private function exec($sql)
    {
        return $this->sqlite->exec($sql);
    }

    private function queryAll($sql)
    {
        $result = [];


        $query = $this->sqlite->query($sql);

        if (!$query) {
            return $result;
        }

        while ($fetch = $query->fetchArray(SQLITE3_NUM)) {
            $result[] = $fetch;
        }

        return $result;
    }
}