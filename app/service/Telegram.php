<?php

namespace app\service;

use support\TelegramBotApi;
use Telegram\Bot\Events\UpdateWasReceived;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Objects\Voice;

class Telegram
{
    /**
     * Фразы для бота.
     */
    public const PHRASES = [
        'welcome' => 'Привет! Я - бот помощник для студентов ДГТУ. Для использования пришли свой E-Mail (логин), привязанный к edu.donstu.ru ',

        'already_exists_user' => 'Этот аккаунт уже привязан! ',

        'start_verify' => 'Я отправил ссылку для авторизации на твой аккаунт. ',

        'check_edumail' => 'Проверь внутреннюю почту (https://edu.donstu.ru/WebApp/#/mail/all) ',
        'edumail_theme' => 'Тишка: Авторизация',
        'edumail_message' => 'Привет! Твоя ссылка для авторизации: [%url%](%url%). Так я смогу убедиться, что Telegram-аккаунт (%name%) принадлежит тебе :)\nВнимание!!! Если ты НЕ пытался войти в бота - НЕ ПЕРЕХОДИ ПО ССЫЛКЕ, это даст пользователю доступ к твоим данным!',

        'account_already_activated' => 'Аккаунт уже активирован!',
        'account_activated_web' => 'Аккаунт активирован. <a href="https://t.me/TeashkaBot">Вернитесь в телеграм</a>',
        'account_activated_tg' => 'Поздравляю! Аккаунт активирован, теперь тебе доступны все функции :)',

        'contact_support' => 'Обратитесь к администратору <a href="https://t.me/GeneralRust">@GeneralRust</a>'
    ];

    public static function downloadVoice(Voice $voice)
    {
        $fileId = $voice->fileId;
        $savePath = base_path() . '/resources/voices/' . $fileId . '.wav';
        $resp = self::api()->downloadFile($fileId, $savePath);
        self::sendMessage($resp);
        self::sendMessage($savePath);
    }

    /**
     * Отправляет сообщение через Telegram API.
     *
     * @param string $text Текст сообщения.
     * @param int|null $chat_id Идентификатор чата. Если не указан, будет использован идентификатор текущего чата.
     * @param array $options Дополнительные параметры сообщения.
     *
     * @return void
     */
    public static function sendMessage($text, $chat_id = null, array $options = []): void
    {
        if (!$chat_id) {
            $chat_id = request()->chat->id;
        }

        $messageData = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => !empty($options['parse_mode']) ? $options['parse_mode'] : 'HTML',
        ];

        self::api()->sendMessage(array_merge($messageData, $options));
    }

    /**
     * Разбирает входные данные запроса и создает объект Update.
     *
     * @param mixed $request Запрос.
     *
     * @return Update Объект Update.
     */
    public static function parseInput($request): Update
    {
        $body = json_decode($request->rawBody(), true);
        $update = new Update($body);

        self::api()->emitEvent(new UpdateWasReceived($update, self::api()));

        return $update;
    }

    /**
     * Возвращает объект TelegramBotApi для выполнения запросов к Telegram API.
     *
     * @return \Telegram\Bot\Api Объект TelegramBotApi.
     */
    public static function api(): TelegramBotApi
    {
        return new TelegramBotApi(config('telegram.token'), config('telegram.async'));
    }
}
