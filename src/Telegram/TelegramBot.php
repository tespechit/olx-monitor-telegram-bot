<?php

namespace App\Telegram;

use App\Olx\OlxAnuncio;

class TelegramBot
{
    protected $bot;

    protected $chat_id;

    public function __construct($token, int $chat_id)
    {
        $this->bot = new \TelegramBot\Api\BotApi($token);
        $this->chat_id = $chat_id;
    }

    public function enviarAnuncio(OlxAnuncio $anuncio)
    {
        $text = sprintf("%s / %s - %d m² - R$ %.2f\n %s",
            $anuncio->cidade,
            $anuncio->bairro,
            $anuncio->area,
            $anuncio->preco,
            $anuncio->url
        );

        $this->bot->sendMessage($this->chat_id, $text);
    }

    public function notificar()
    {
        $this->bot->sendChatAction($this->chat_id, 'typing');
    }

    public function enviarAnuncios(array $anuncios)
    {
        foreach ($anuncios as $anuncio) {
            $this->enviarAnuncio($anuncio);
        }
    }
}