<?php

namespace app\service;

use Exception;
use support\TelegramBotApi;
use Telegram\Bot\Events\UpdateWasReceived;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;

class Telegram
{
    public static TelegramBotApi $api;

    /**
     * Загружает голосовое сообщение из Telegram и сохраняет его на сервер.
     *
     * @param Message $message Объект сообщения.
     * @return string Путь к сохраненному голосовому сообщению.
     * @throws Exception Если произошла ошибка при загрузке голосового сообщения.
     */
    public static function downloadVoice(Message $message): string
    {
        $filePath = self::api()->getFile(['file_id' => $message->voice->fileId])->filePath;
        $fileUrl = 'https://api.telegram.org/file/bot' . config('telegram.token') . '/' . $filePath;
        $savePath = base_path("resources/voices/{$message->chat->id}_" . basename($filePath));

        $fileContent = file_get_contents($fileUrl);
        if ($fileContent !== false && file_put_contents($savePath, $fileContent) !== false) {
            return $savePath;
        } else {
            self::sendMessage("Ошибка загрузки голосового сообщения");
            throw new Exception('Ошибка загрузки голосового сообщения');
        }
    }

    /**
     * Отправляет сообщение через Telegram API.
     *
     * @param string $text Текст сообщения.
     * @param int|null $chat_id Идентификатор чата. Если не указан, будет использован идентификатор текущего чата.
     * @param array $options Дополнительные параметры сообщения.
     * @return void
     */
    public static function sendMessage(string $text, ?int $chat_id = null, array $options = []): void
    {
        $chat_id = $chat_id ?? self::getCurrentChatId();

        $messageData = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => $options['parse_mode'] ?? 'HTML',
        ];

        self::api()->sendMessage(array_merge($messageData, $options));
    }


    /**
     * Отправляет фото через Telegram API.
     *
     * @param string $photo Фото.
     * @param int|null $chat_id Идентификатор чата. Если не указан, будет использован идентификатор текущего чата.
     * @param array $options Дополнительные параметры сообщения.
     * @return void
     */
    public static function sendPhoto(string $photo, ?int $chat_id = null, array $options = []): void
    {
        $chat_id = $chat_id ?? self::getCurrentChatId();

        $messageData = [
            'chat_id' => $chat_id,
            'photo' =>  InputFile::create($photo),
        ];

        self::api()->sendPhoto(array_merge($messageData, $options));
    }

    /**
     * Разбирает входные данные запроса и создает объект Update.
     *
     * @param mixed $request Запрос.
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
     * @return TelegramBotApi Объект TelegramBotApi.
     */
    public static function api(): TelegramBotApi
    {
        if (!isset(self::$api)) {
            self::$api = new TelegramBotApi(config('telegram.token'), config('telegram.async'));
        }

        return self::$api;
    }

    /**
     * Возвращает идентификатор текущего чата.
     *
     * @return int|null Идентификатор текущего чата или null, если он не найден.
     */
    private static function getCurrentChatId(): ?int
    {
        return request()->chat->id ?? null;
    }
}
