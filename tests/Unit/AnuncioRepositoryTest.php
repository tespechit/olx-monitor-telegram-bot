<?php

use App\AnunciosRepository;
use App\Olx\OlxAnuncio;
use PHPUnit\Framework\TestCase;

class AnuncioRepositoryTest extends TestCase
{
    /**
     * @var AnunciosRepository
     */
    protected $repository;

    public function test_cria_schema()
    {
        $result = $this->repository->criarSchema();

        $this->assertTrue($result);
    }

    public function test_salva_anuncio()
    {
        $this->repository->criarSchema();

        $result = $this->repository->save($this->makeAnuncio());

        $this->assertTrue($result);
    }

    public function test_retorna_anuncios()
    {
        $this->repository->criarSchema();

        $anuncio = $this->makeAnuncio();

        $this->repository->save($anuncio);

        $anuncios = $this->repository->all();

        $this->assertCount(1, $anuncios);

        $this->assertEquals($anuncios[0]->id, $anuncio->id);
    }

    public function test_retorna_anuncios_ids()
    {
        $this->repository->criarSchema();

        $anuncio = $this->makeAnuncio();

        $this->repository->save($anuncio);

        $anuncios = $this->repository->byId([$anuncio->id]);

        $this->assertCount(1, $anuncios);

        $this->assertEquals($anuncios[0]->id, $anuncio->id);
    }

    protected function makeAnuncio()
    {
        return new OlxAnuncio([
            'id' => '123123123',
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
    }

    protected function setUp()
    {
        $this->repository = new AnunciosRepository(new \PDO('sqlite::memory:'));
    }
}