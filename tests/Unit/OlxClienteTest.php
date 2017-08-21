<?php

use App\Olx\OlxAnuncio;
use App\Olx\OlxCliente;
use App\Olx\OlxCriterio;
use PHPUnit\Framework\TestCase;

class OlxClienteTest extends TestCase
{
    /**
     * @var OlxCliente
     */
    protected $olx;

    public function setUp()
    {
        $this->olx = new OlxCliente(new OlxCriterio(), [
            __DIR__ . '/../resources/olx_anuncio-123123123.html'
        ]);
    }

    public function test_parse_anuncio()
    {
        $olx_anuncio = __DIR__ . '/../resources/olx_anuncio-123123123.html';

        $anuncio = $this->invokeMethod($this->olx, 'parseAnuncio', [$olx_anuncio]);

        $this->assertInstanceOf(OlxAnuncio::class, $anuncio);

        $atributos = [
            'id' => 123123123,
            'titulo' => 'Casa em Campo Grande',
            'preco' => 1200,
            'quartos' => 3,
            'area' => 55,
            'condominio' => 0,
            'vagas_garagem' => 1,
            'cidade' => 'Recife',
            'bairro' => 'Campo Grande',
            'cep' => '52040050',
        ];

        foreach ($atributos as $atributo => $valor) {
            $this->assertEquals($anuncio->{$atributo}, $valor);
        }

        $this->assertNotFalse(DateTime::createFromFormat('Y-m-d H:i:s', $anuncio->created_at));
    }

    public function test_detectar_quantidade_paginas()
    {
        $olx_lista_anuncios = __DIR__ . '/../resources/olx-lista-anuncios.html';

        $num_paginas = $this->invokeMethod($this->olx, 'getQuantidadePaginas', [$olx_lista_anuncios]);

        $this->assertEquals($num_paginas, 9);
    }

    public function test_get_anuncios_urls()
    {
        $olx_lista_anuncios = __DIR__ . '/../resources/olx-lista-anuncios.html';

        $urls = $this->invokeMethod($this->olx, 'getAnunciosUrls', [$olx_lista_anuncios]);

        $this->assertTrue(is_array($urls), 'Não é um array');
        $this->assertCount(50, $urls);
    }

    public function test_get_anuncios()
    {
        $olx_anuncio = __DIR__ . '/../resources/olx_anuncio-123123123.html';

        $criterio = (new OlxCriterio())->setPreco(100, 1200);

        $anuncios = $this->invokeMethod($this->olx, 'getAnuncios', [$criterio, [$olx_anuncio]]);

        $this->assertCount(1, $anuncios);
        $this->assertInstanceOf(OlxAnuncio::class, $anuncios[0]);
    }

    protected function invokeMethod($object, $methodName, $args)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }
}