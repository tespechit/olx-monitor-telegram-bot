<?php

namespace App\Olx;

class OlxCriterio
{
    public $preco_min;
    public $preco_max;
    public $area_min;
    public $area_max;
    public $quartos_min;
    public $quartos_max;
    public $garagem;

    public function __construct(array $criterios = [])
    {
        $keys = ['preco_min', 'preco_max', 'area_min', 'area_max', 'quartos_min', 'quartos_max', 'garagem'];

        $propriedades = array_intersect_key($criterios, array_flip($keys));

        foreach ($propriedades as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function setArea(int $min, int $max = PHP_INT_MAX)
    {
        $this->area_min = $min;
        $this->area_max = $max;

        return $this;
    }

    public function setGaragem()
    {
        $this->garagem = true;

        return $this;
    }

    public function setPreco(int $min, int $max = PHP_INT_MAX)
    {
        $this->preco_min = $min;
        $this->preco_max = $max;

        return $this;
    }

    public function setQuartos(int $min, int $max = PHP_INT_MAX)
    {
        $this->quartos_min = $min;
        $this->quartos_max = $max;

        return $this;
    }

    public function validar(OlxAnuncio $anuncio)
    {
        if (!$this->estaEntre($anuncio->preco, $this->preco_min, $this->preco_max)) {
            return false;
        }

        if (!$this->estaEntre($anuncio->area, $this->area_min, $this->area_max)) {
            return false;
        }

        if (!$this->estaEntre($anuncio->quartos, $this->quartos_min, $this->quartos_max)) {
            return false;
        }

        if ($this->garagem && $anuncio->vagas_garagem == 0) {
            return false;
        }

        return true;
    }

    private function estaEntre($campo_anuncio, $campo_criterio_min, $campo_criterio_max)
    {
        if (is_null($campo_anuncio)) {
            return true;
        }

        if (is_null($campo_criterio_min) && is_null($campo_criterio_max)) {
            return true;
        }

        $campo_criterio_min = $campo_criterio_min ?? PHP_INT_MIN;

        if ($campo_anuncio < $campo_criterio_min) {
            return false;
        }

        $campo_criterio_max = $campo_criterio_max ?? PHP_INT_MAX;

        if ($campo_anuncio > $campo_criterio_max) {
            return false;
        }

        return true;
    }
}