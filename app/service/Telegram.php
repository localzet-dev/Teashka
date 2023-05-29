<?php

namespace app\service;

use support\TelegramBotApi;
use Telegram\Bot\Events\UpdateWasReceived;
use Telegram\Bot\Objects\Update;

class Telegram
{
    public static function parseInput($request)
    {
        $body = json_decode($request->rawBody(), true);
        $update = new Update($body);

        self::api()->emitEvent(new UpdateWasReceived($update, self::api()));

        return $update;
    }

    /**
     * @return \Telegram\Bot\Api
     */
    public static function api()
    {
        return new TelegramBotApi(config('telegram.token'), config('telegram.async'));
    }
}
