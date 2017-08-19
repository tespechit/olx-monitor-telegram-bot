<?php

namespace App;

use App\Olx\OlxCliente;
use App\Olx\OlxCriterio;
use App\Telegram\TelegramBot;

class ProcurarAnuncios
{
    public static function run(OlxCliente $olx, AnunciosRepository $repository, TelegramBot $bot)
    {
        $bot->notificar();

        $anuncios = $olx->procurar(false, 5);

        $ids = array_map(function ($anuncio) {
            return $anuncio->id;
        }, $anuncios);

        $anuncios_db = $repository->byId($ids);

        $novos_anuncios =  array_udiff($anuncios, $anuncios_db, function ($a, $b) {
            return $a->id - $b->id;
        });

        $bot->enviarAnuncios($novos_anuncios);

        $repository->saveMany($novos_anuncios);

        return true;
    }
}