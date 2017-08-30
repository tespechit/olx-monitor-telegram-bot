<?php

// Add this line to your crontab file:
// * * * * * cd /path/to/project && php jobby.php 1>> /dev/null 2>&1

use App\AnunciosRepository;
use App\Olx\OlxCliente;
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

        $black_list = [
            'candeias', 'torre', 'iputinga', 'linha-do-tiro', 'arruda', 'boa-vista',
            'ipsep', 'santo-amaro', 'imbiribeira', 'varzea', 'caxanga', 'igarassu', 'olinda',
            'dom-helder'
        ];

        $db_path = __DIR__ . '/db.sqlite';
        $criar_schema = !file_exists($db_path);

        $repository = new AnunciosRepository(new \PDO('sqlite:' . $db_path));

        if ($criar_schema) {
            $repository->criarSchema();
        }

        $bot = new Bot($_ENV['telegram_token'], $chat_id);
        $bot->notificar();

        $olx = new OlxCliente();

        foreach ($urls as $url) {
            $anuncios_urls = $olx->getAnunciosUrls($url, false);

            $anuncios_urls = array_filter($anuncios_urls, function ($url) use ($black_list) {
                foreach ($black_list as $item) {
                    if (strpos($url, $item) !== false)
                        return false;
                }

                return true;
            });

            $anuncios = [];
            foreach ($anuncios_urls as $anuncio_url) {
                try {
                    $anuncio = $olx->getAnuncio($anuncio_url);

                    if (!$criterio->validar($anuncio)) {
                        continue;
                    }

                    $anuncios[] = $anuncio;
                } catch (Exception $e) {
                }
            }

            $ids = array_map(function ($anuncio) {
                return $anuncio->id;
            }, $anuncios);

            $anuncios_db = $repository->byId($ids);

            $novos_anuncios = array_udiff($anuncios, $anuncios_db, function ($a, $b) {
                return $a->id - $b->id;
            });

            $bot->enviarAnuncios($novos_anuncios);

            $repository->saveMany($novos_anuncios);
        }

        return true;
    },

    'output' => 'logs/ProcurarAnuncios.log',
]);

if (in_array('--run', $argv)) {
    $jobby->getJobs()['ProcurarAnuncios']['closure']();
    exit;
}

$jobby->run();