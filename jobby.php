<?php

// Add this line to your crontab file:
// * * * * * cd /path/to/project && php jobby.php 1>> /dev/null 2>&1

use App\AnunciosRepository;
use App\Olx\OlxCriterio;
use App\ProcurarAnuncios;
use App\Telegram\Bot;
use Dotenv\Dotenv;
use Jobby\Jobby;

require_once __DIR__ . '/vendor/autoload.php';

if (isset(getopt('', ['criar-schema::'])['criar-schema'])) {
    $pdo = new \PDO('sqlite:' . __DIR__ . '/db.sqlite');

    $repository = new AnunciosRepository($pdo);

    $repository->criarSchema();
    exit;
}

$jobby = new Jobby();

$jobby->add('ProcurarAnuncios', [

    'schedule' => '*/10 * * * *',

    'maxRuntime' => '300',

    'command' => function () {

        $env = new Dotenv(__DIR__);
        $env->load();

        $env->required(['telegram_token']);

        $chat_id = 62448110;

        $criterio = (new OlxCriterio())
            ->setPreco(400, 850)
            ->setArea(50, 120)
            ->setQuartos(2);

        $urls = [
            'http://pe.olx.com.br/grande-recife/grande-recife/jaboatao-dos-guararapes/imoveis/aluguel/casas',
            'http://pe.olx.com.br/grande-recife/recife/imoveis/aluguel/casas',
        ];

        return ProcurarAnuncios::run(
            new App\Olx\OlxCliente($criterio, $urls),
            new AnunciosRepository(new \PDO('sqlite:' . __DIR__ . '/db.sqlite')),
            new Bot($_ENV['telegram_token'], $chat_id)
        );
    },

    'output' => 'logs/ProcurarAnuncios.log',
]);

$jobby->run();