<?php

use App\AnunciosRepository;
use App\Olx\OlxCliente;
use App\Olx\OlxCriterio;
use App\ProcurarAnuncios;
use App\Telegram\TelegramBot;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

class ProcurarAnunciosTest extends TestCase
{
    /**
     * @var TelegramBot
     */
    protected $bot;

    /**
     * @var AnunciosRepository
     */
    protected $repository;

    public function setUp()
    {
        $env = new Dotenv(__DIR__ . '/../../');
        $env->load();
        $env->required(['telegram_token']);

        $dsn = 'sqlite::memory:';
        $this->repository = new AnunciosRepository(new \PDO($dsn));
        $this->repository->criarSchema();

        $this->bot = new TelegramBot($_ENV['telegram_token'], 62448110);
    }

    public function test_procurar_anuncios()
    {
        $url = 'http://pe.olx.com.br/grande-recife/recife/imoveis/aluguel/casas';

        $olx = new OlxCliente(new OlxCriterio(), [$url]);

        $result = ProcurarAnuncios::run(
            $olx,
            $this->repository,
            $this->bot
        );

        $this->assertTrue($result);
    }
}