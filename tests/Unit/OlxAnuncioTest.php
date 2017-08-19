<?php

use App\Olx\OlxAnuncio;
use PHPUnit\Framework\TestCase;

class OlxAnuncioTest extends TestCase
{

    public function test_consegue_instanciar()
    {
        $anuncio = new OlxAnuncio([
            'id' => 123123123,
            'titulo' => 'Titulo Anúncio 2/4',
            'url' => 'http://olx.com.br/anuncio',
            'preco' => 800,
            'quartos' => 2,
            'area' => 65,
            'condominio' => 0,
            'carros' => 1,
            'cidade' => 'Recife',
            'bairro' => 'Cidade Universitária',
            'cep' => '50740900',
        ]);

        $this->assertEquals($anuncio->id, 123123123);
        $this->assertEquals($anuncio->titulo, 'Titulo Anúncio 2/4');
        $this->assertEquals($anuncio->url, 'http://olx.com.br/anuncio');
        $this->assertEquals($anuncio->preco, 800);
        $this->assertEquals($anuncio->quartos, 2);
        $this->assertEquals($anuncio->area, 65);
        $this->assertEquals($anuncio->condominio, 0);
        $this->assertEquals($anuncio->cidade, 'Recife');
        $this->assertEquals($anuncio->bairro, 'Cidade Universitária');
        $this->assertEquals($anuncio->cep, '50740900');
    }
}