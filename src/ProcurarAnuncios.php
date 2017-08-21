<?php

namespace App;

use App\Olx\OlxCliente;
use App\Olx\OlxCriterio;
use App\Telegram\Bot;

class ProcurarAnuncios
{
    public static function run(OlxCliente $olx, AnunciosRepository $repository, Bot $bot)
    {
        $bot->notificar();

        $anuncios = $olx->procurarAnuncios(true, 5);

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