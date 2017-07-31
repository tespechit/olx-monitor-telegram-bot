<?php

// Add this line to your crontab file:
// * * * * * cd /path/to/project && php jobby.php 1>> /dev/null 2>&1

use App\AnunciosRepository;
use App\OlxCliente;
use App\TelegramBot;
use Dotenv\Dotenv;
use Jobby\Jobby;

require_once __DIR__ . '/vendor/autoload.php';

$env = new Dotenv(__DIR__);
$env->load();

$telegram_contas = [
    [
        'chat_id' => 62448110,
        'urls' => [
            'http://pe.olx.com.br/grande-recife/grande-recife/jaboatao-dos-guararapes/imoveis/aluguel/casas',
            'http://pe.olx.com.br/grande-recife/recife/imoveis/aluguel',
        ],
        'limite_paginas' => 5,
        'criterios' => [
            'preco_min' => 450,
            'preco_max' => 850,
            'area_min' => 40,
            'area_max' => 120,
            'quartos_min' => 2,
        ],
    ],
];

$jobby = new Jobby();

$command = function () use ($telegram_contas) {

    $bot = new TelegramBot($_ENV['TELEGRAM_TOKEN']);

    $db_path = __DIR__ . '/db.sqlite';

    if (!is_readable($db_path)) {
        AnunciosRepository::criarSchema($db_path);
    }

    $db = new AnunciosRepository($db_path);

    foreach ($telegram_contas as $conta) {
        $olx = new OlxCliente($conta['urls'], $conta['limite_paginas'] ?? 5);

        $criterios = $conta['criterios'];

        $anuncios = $olx->procurar(
            $criterios['preco_min'],
            $criterios['preco_max'],
            $criterios['area_min'],
            $criterios['area_max'],
            $criterios['quartos_min']
        );

        $anuncios_db = $db->byId(
            array_column($anuncios, 'id')
        );

        $diff = array_udiff($anuncios, $anuncios_db, function ($a, $b) {
            return strlen(current($a)) - strlen(current($b));
        });

        if (empty($diff)) {
            continue;
        }

        foreach ($diff as $anuncio) {
            $bot->enviarAnuncio($conta['chat_id'], $anuncio);
        }

        $db->save($diff);
    }

    return true;
};

if (in_array('teste', array_keys(getopt('', ['teste::'])))) {
    $command();
    exit;
}

$jobby->add('EncontrarAnuncios', [
    'command' => $command,
    'schedule' => '*/10 * * * *',
    'output' => 'logs/EncontrarAnuncios.log',
    'enabled' => true,
]);

$jobby->run();
