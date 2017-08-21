<?php

use App\Olx\OlxAnuncio;
use PHPUnit\Framework\TestCase;

class OlxAnuncioTest extends TestCase
{

    public function test_consegue_instanciar()
    {
        $atributos = [
            'id' => 123123123,
            'titulo' => 'Titulo Anúncio 2/4',
            'url' => 'http://olx.com.br/anuncio',
            'preco' => 800,
            'quartos' => 2,
            'area' => 65,
            'condominio' => 0,
            'vagas_garagem' => 1,
            'cidade' => 'Recife',
            'bairro' => 'Cidade Universitária',
            'cep' => '50740900',
        ];

        $anuncio = new OlxAnuncio($atributos);

        foreach ($atributos as $atributo => $valor) {
            $this->assertEquals($anuncio->{$atributo}, $valor);
        }
    }
}