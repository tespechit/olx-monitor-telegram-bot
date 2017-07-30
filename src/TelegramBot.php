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

        $titulo = $anuncio['titulo'];
        $area = $anuncio['area'] . ' m²';
        $preco = 'R$ ' . number_format($anuncio['preco'], 2, ',', '.');
//        $data = \DateTime::createFromFormat('Y-m-d H:i:s', $anuncio['created_at'])->format('H:i d/m/Y');


        $titulo = utf8_encode($titulo);

        $text = "$titulo - $area - $preco \n" .
            $anuncio['url'];

        $this->bot->sendMessage($chat_id, $text);

//        $url = parse_url($anuncio['foto']);
//        if (isset($url['host']) && isset($url['path'])) {
//            $url = $url['host'] . $url['path'];
//            $this->bot->sendPhoto($chat_id, $url);
//        }

    }

    public function indicarPesquisa($chat_id)
    {
        $this->bot->sendChatAction($chat_id, 'find_location');
    }
}