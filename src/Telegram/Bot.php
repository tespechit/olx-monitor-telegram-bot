<?php

namespace App\Telegram;

use App\Olx\OlxAnuncio;

class Bot
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
        $total_aluguel = (float) $anuncio->preco + (float) $anuncio->condominio;

        $text = sprintf("%s / %s / %s\n R$ %.2f - %d m²\n %s",
            $anuncio->cidade,
            $anuncio->bairro,
            $anuncio->cep,
            $total_aluguel,
            $anuncio->area,
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