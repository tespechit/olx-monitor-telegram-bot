<?php

namespace App;

use App\Olx\OlxAnuncio;

class AnunciosRepository extends Repository
{
    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
    }

    public function criarSchema()
    {
        $this->exec('PRAGMA encoding="UTF-8"');

        $sql = 'CREATE TABLE anuncios
                (
                    id INT,
                    titulo TEXT,
                    url TEXT,
                    preco INT,
                    quartos INT,
                    area INT,
                    condominio INT,
                    vagas_garagem INT,
                    cidade TEXT,
                    bairro TEXT,
                    cep INT,
                    created_at DATETIME
                )';

        return $this->exec($sql) === 0;
    }

    public function all()
    {
        $sql = 'SELECT 
                  id, titulo, url, preco, quartos, area, condominio, vagas_garagem, cidade, bairro, cep, created_at 
                FROM anuncios';

        return $this->queryAll($sql, [], 'App\Olx\OlxAnuncio');
    }

    public function byId($ids)
    {
        $sql = "SELECT id, titulo, url, preco, quartos, area, condominio, vagas_garagem, cidade, bairro, cep, created_at 
                FROM anuncios 
                WHERE id IN ('" . implode("','", $ids) . "')";

        return $this->queryAll($sql, [], 'App\Olx\OlxAnuncio');
    }

    public function save(OlxAnuncio $anuncio)
    {
        $sql = 'INSERT INTO anuncios (
                  id, titulo, url, preco, quartos,
                  area, condominio, vagas_garagem, cidade, bairro,
                  cep, created_at
                ) VALUES (
                  :id, :titulo, :url, :preco, :quartos,
                  :area, :condominio, :vagas_garagem, :cidade, :bairro, 
                  :cep, :created_at
                )';

        return $this->exec($sql, [
                'id' => $anuncio->id,
                'titulo' => $anuncio->titulo,
                'url' => $anuncio->url,
                'preco' => $anuncio->preco,
                'quartos' => $anuncio->quartos,
                'area' => $anuncio->area,
                'condominio' => $anuncio->condominio,
                'vagas_garagem' => $anuncio->vagas_garagem,
                'cidade' => $anuncio->cidade,
                'bairro' => $anuncio->bairro,
                'cep' => $anuncio->cep,
                'created_at' => date('Y-m-d H:i:s'),
            ]) === 1;
    }

    public function saveMany(array $anuncios)
    {
        foreach ($anuncios as $anuncio) {
            $this->save($anuncio);
        }
    }
}