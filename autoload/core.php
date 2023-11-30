<?php

use app\service\Telegram;
use Telegram\Bot\Exceptions\TelegramSDKException;

/**
 * @throws TelegramSDKException
 */
function telegram(): Telegram
{
    return request()->telegram ?? new Telegram(config('telegram.token'));
}