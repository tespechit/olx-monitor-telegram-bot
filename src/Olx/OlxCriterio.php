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
        if ($this->isCampoMenor($anuncio->preco, $this->preco_min)) {
            return false;
        }

        if ($this->isCampoMaior($anuncio->preco, $this->preco_max)) {
            return false;
        }

        if ($this->isCampoMenor($anuncio->area, $this->area_min)) {
            return false;
        }

        if ($this->isCampoMaior($anuncio->area, $this->area_max)) {
            return false;
        }

        if ($this->isCampoMenor($anuncio->quartos, $this->quartos_min)) {
            return false;
        }

        if ($this->isCampoMaior($anuncio->quartos, $this->quartos_max)) {
            return false;
        }

        if ($this->garagem && $anuncio->vagas_garagem == 0) {
            return false;
        }

        return true;
    }

    private function isCampoMenor($campo_anuncio, $campo_criterio)
    {
        if (is_null($campo_criterio)) {
            return false;
        }

        if (is_null($campo_anuncio)) {
            return true;
        }

        if ($campo_anuncio < $campo_criterio) {
            return true;
        }

        return false;
    }

    private function isCampoMaior($campo_anuncio, $campo_criterio)
    {
        if (is_null($campo_criterio)) {
            return false;
        }

        if (is_null($campo_anuncio)) {
            return true;
        }

        if ($campo_anuncio > $campo_criterio) {
            return true;
        }

        return false;
    }
}