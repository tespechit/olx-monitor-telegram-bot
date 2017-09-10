<?php

namespace App\Telegram;

class Bot
{
    protected $bot;

    protected $chat_id;

    public function __construct($token, int $chat_id)
    {
        $this->bot = new \TelegramBot\Api\BotApi($token);
        $this->chat_id = $chat_id;
    }

    public function sendMessage($text)
    {
        $this->bot->sendMessage($this->chat_id, $text);
    }

    public function notificar()
    {
        $this->bot->sendChatAction($this->chat_id, 'typing');
    }
}