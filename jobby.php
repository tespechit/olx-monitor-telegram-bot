<?php

// Add this line to your crontab file:
// * * * * * cd /path/to/project && php jobby.php 1>> /dev/null 2>&1

use App\AnunciosRepository;
use App\Olx\OlxCriterio;
use App\Telegram\Bot;
use Dotenv\Dotenv;
use Jobby\Jobby;

require_once __DIR__ . '/vendor/autoload.php';

$jobby = new Jobby();

$jobby->add('ProcurarAnuncios', [

    'schedule' => '*/5 * * * *',

    'maxRuntime' => '240',

    'command' => function () {

        $env = new Dotenv(__DIR__);
        $env->load();

        $env->required(['telegram_token']);

        $chat_id = 62448110;

        $criterio = (new OlxCriterio())
            ->setPreco(400, 850)
            ->setArea(40)
            ->setQuartos(2);

        $urls = [
            'http://pe.olx.com.br/grande-recife/grande-recife/jaboatao-dos-guararapes/imoveis/aluguel',
            'http://pe.olx.com.br/grande-recife/recife/imoveis/aluguel',
        ];

        $db_path = __DIR__ . '/db.sqlite';
        $criar_schema = !file_exists($db_path);

        $repository = new AnunciosRepository(new \PDO('sqlite:' . $db_path));

        if ($criar_schema) {
            $repository->criarSchema();
        }

        $bot = new Bot($_ENV['telegram_token'], $chat_id);
        $bot->notificar();

        $olx = new App\Olx\OlxCliente($criterio, $urls);
        $anuncios = $olx->procurarAnuncios();

        $ids = array_map(function ($anuncio) {
            return $anuncio->id;
        }, $anuncios);

        $anuncios_db = $repository->byId($ids);

        $novos_anuncios = array_udiff($anuncios, $anuncios_db, function ($a, $b) {
            return $a->id - $b->id;
        });

        $bot->enviarAnuncios($novos_anuncios);

        $repository->saveMany($novos_anuncios);
    },

    'output' => 'logs/ProcurarAnuncios.log',
]);

$jobby->run();