<?php

use App\Olx\OlxAnuncio;
use App\Olx\OlxCriterio;
use PHPUnit\Framework\TestCase;

class OlxCriterioTest extends TestCase
{

    public function test_consegue_instanciar()
    {
        $atributos = [
            'preco_min' => 5,
            'preco_max' => 10,
            'area_min' => 10,
            'area_max' => 20,
            'quartos_min' => 15,
            'quartos_max' => 30,
            'garagem' => true,
        ];

        $criterio = new OlxCriterio($atributos);

        foreach ($atributos as $atributo => $valor)
        {
            $this->assertEquals($criterio->{$atributo}, $valor);
        }
    }

    public function test_set_preco()
    {
        $criterio = new OlxCriterio();

        $criterio->setPreco(1, 10);

        $this->assertEquals($criterio->preco_min, 1);
        $this->assertEquals($criterio->preco_max, 10);
    }

    public function test_set_area()
    {
        $criterio = new OlxCriterio();

        $criterio->setArea(1, 10);

        $this->assertEquals($criterio->area_min, 1);
        $this->assertEquals($criterio->area_max, 10);
    }

    public function test_set_quartos()
    {
        $criterio = new OlxCriterio();

        $criterio->setQuartos(1, 10);

        $this->assertEquals($criterio->quartos_min, 1);
        $this->assertEquals($criterio->quartos_max, 10);
    }

    public function test_set_garagem()
    {
        $criterio = new OlxCriterio();

        $this->assertNull($criterio->garagem);

        $criterio->setGaragem();

        $this->assertTrue($criterio->garagem);
    }

    public function test_valida_true_com_anuncio_e_criterio_null()
    {
        $anuncio = new OlxAnuncio([]);

        $criterio = new OlxCriterio([]);

        $this->assertTrue($criterio->validar($anuncio));
    }

    public function test_valida_anuncio_preco()
    {
        $criterio = (new OlxCriterio())
            ->setPreco(5, 10);

        $anuncio_preco_abaixo = new OlxAnuncio([ 'preco' => 4 ]);
        $this->assertFalse($criterio->validar($anuncio_preco_abaixo));

        $anuncio_preco_acima = new OlxAnuncio([ 'preco' => 11 ]);
        $this->assertFalse($criterio->validar($anuncio_preco_acima));

        $anuncio_ok = new OlxAnuncio(['preco' => 8]);
        $this->assertTrue($criterio->validar($anuncio_ok));
    }

    public function test_valida_anuncio_area()
    {
        $criterio = (new OlxCriterio())
            ->setArea(5, 10);

        $anuncio_area_abaixo = new OlxAnuncio(['area' => 4]);
        $this->assertFalse($criterio->validar($anuncio_area_abaixo));

        $anuncio_area_acima = new OlxAnuncio([ 'area' => 11 ]);
        $this->assertFalse($criterio->validar($anuncio_area_acima));

        $anuncio_ok = new OlxAnuncio(['area' => 8]);
        $this->assertTrue($criterio->validar($anuncio_ok));
    }

    public function test_valida_anuncio_quartos()
    {
        $criterio = (new OlxCriterio())
            ->setQuartos(2, 3);

        $anuncio_quartos_abaixo = new OlxAnuncio(['quartos' => 1]);
        $this->assertFalse($criterio->validar($anuncio_quartos_abaixo));

        $anuncio_quartos_acima = new OlxAnuncio([ 'quartos' => 4 ]);
        $this->assertFalse($criterio->validar($anuncio_quartos_acima));

        $anuncio_ok = new OlxAnuncio(['quartos' => 3]);
        $this->assertTrue($criterio->validar($anuncio_ok));
    }

    public function test_valida_anuncio_garagem()
    {
        $criterio = (new OlxCriterio())
            ->setGaragem();

        $anuncio_sem_garagem = new OlxAnuncio([]);
        $this->assertFalse($criterio->validar($anuncio_sem_garagem));

        $anuncio_com_garagem = new OlxAnuncio(['vagas_garagem' => 1]);
        $this->assertTrue($criterio->validar($anuncio_com_garagem));
    }
}