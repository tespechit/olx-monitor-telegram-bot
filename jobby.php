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


$jobby = new Jobby();

$command = function () {

    $chat_id = 62448110;

    $bot = new TelegramBot($_ENV['TELEGRAM_TOKEN']);

    $olx = new OlxCliente([
        'http://pe.olx.com.br/grande-recife/grande-recife/jaboatao-dos-guararapes/imoveis/aluguel',
        'http://pe.olx.com.br/grande-recife/recife/imoveis/aluguel',
    ]);

    $db_path = __DIR__ . '/db.sqlite';

    if (!is_readable($db_path)) {
        AnunciosRepository::criarSchema($db_path);
    }

    $db = new AnunciosRepository($db_path);

    $anuncios = $olx->procurar(400, 850, 40, 120, 2);

    $anuncios_db = $db->byId(
        array_column($anuncios, 'id')
    );

    $diff = array_udiff($anuncios, $anuncios_db, function ($a, $b) {
        return strlen(current($a)) - strlen(current($b));
    });


    if (empty($diff)) {
        return true;
    }


    foreach ($diff as $anuncio) {
        $bot->enviarAnuncio($chat_id, $anuncio);
    }

    $db->save($diff);

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
