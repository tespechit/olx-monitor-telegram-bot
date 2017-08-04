<?php

namespace App;

class TelegramBot
{
    protected $bot;

    public function __construct($token)
    {
        $this->bot = new \TelegramBot\Api\BotApi($token);
    }

    public function enviarAnuncio($chat_id, $anuncio)
    {
        $cidade = $anuncio['cidade'];
        $bairro = $anuncio['bairro'];
        $area = $anuncio['area'] . ' m²';
        $preco = 'R$ ' . number_format($anuncio['preco'], 2, ',', '.');

        $text = " $cidade / $bairro - $area - $preco \n" .
            $anuncio['url'];

        $this->bot->sendMessage($chat_id, $text);

    }

    public function indicarPesquisa($chat_id)
    {
        $this->bot->sendChatAction($chat_id, 'find_location');
    }
}