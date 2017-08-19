<?php

namespace App\Olx;

class OlxAnuncio
{
    public $id;
    public $titulo;
    public $url;
    public $preco;
    public $quartos;
    public $area;
    public $condominio;
    public $vagas_garagem;
    public $cidade;
    public $bairro;
    public $cep;
    public $created_at;

    public function __construct(array $dados = [])
    {
        $keys = [
            'id',
            'titulo',
            'url',
            'preco',
            'quartos',
            'area',
            'condominio',
            'vagas_garagem',
            'cidade',
            'bairro',
            'cep',
            'created_at'
        ];

        $propriedades = array_intersect_key($dados, array_flip($keys));

        foreach ($propriedades as $key => $value) {
            $this->{$key} = $value;
        }
    }

}