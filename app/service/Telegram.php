<?php

namespace app\service;

use Exception;
use support\telegram\GuzzleHttpClient;
use Telegram\Bot\Api;
use Telegram\Bot\Events\UpdateWasReceived;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;
use Triangle\Engine\Http\Request;

/**
 * Класс для работы с Telegram API.
 */
class Telegram
{
    /**
     * @var Api Объект для работы с Telegram API.
     */
    private Api $api;

    /**
     * Конструктор класса.
     *
     * @param string $token Токен бота.
     * @param int|null $chatId Идентификатор чата.
     * @throws TelegramSDKException
     */
    public function __construct(string $token, ?int $chatId = null)
    {
        $this->api = new Api($token, false, new GuzzleHttpClient());
    }

    /**
     * Загружает файл из Telegram.
     *
     * @param string $fileId Идентификатор файла.
     * @return string URL файла.
     * @throws TelegramSDKException
     */
    private function downloadFile(string $fileId): string
    {
        $filePath = $this->api->getFile(['file_id' => $fileId])->filePath;
        return 'https://api.telegram.org/file/bot' . getenv('TG_TOKEN') . '/' . $filePath;
    }

    /**
     * Сохраняет файл на сервере.
     *
     * @param string $fileUrl URL файла.
     * @param string $savePath Путь для сохранения файла.
     * @return string Путь к сохраненному файлу.
     * @throws Exception Если произошла ошибка при сохранении файла.
     */
    private function saveFile(string $fileUrl, string $savePath): string
    {
        $fileContent = file_get_contents($fileUrl);
        if ($fileContent !== false && file_put_contents($savePath, $fileContent) !== false) {
            return $savePath;
        } else {
            throw new Exception('Ошибка загрузки файла');
        }
    }

    /**
     * Загружает голосовое сообщение из Telegram и сохраняет его на сервере.
     *
     * @param Message $message Объект сообщения.
     * @return string Путь к сохраненному голосовому сообщению.
     * @throws Exception Если произошла ошибка при загрузке голосового сообщения.
     */
    public function downloadVoice(Message $message): string
    {
        $fileUrl = $this->downloadFile($message->voice->fileId);
        $savePath = base_path("public/voices/{$message->chat->id}_" . basename($fileUrl));

        try {
            return $this->saveFile($fileUrl, $savePath);
        } catch (Exception $e) {
            $this->sendMessage("Ошибка загрузки голосового сообщения", $message->chat->id);
            throw $e;
        }
    }

    /**
     * Отправляет сообщение через Telegram API.
     *
     * @param string $text Текст сообщения.
     * @param array $options Дополнительные параметры сообщения.
     * @throws TelegramSDKException
     */
    public function sendMessage(string $text, int $chat_id, array $options = []): void
    {
        $messageData = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => $options['parse_mode'] ?? 'HTML',
        ];

        $this->api->sendMessage(array_merge($messageData, $options));
    }

    /**
     * Разбирает входные данные запроса и создает объект Update.
     *
     * @param Request $request Запрос.
     * @return Update Объект Update.
     */
    public function parseInput(Request $request): Update
    {
        $body = json_decode($request->rawBody(), true);
        $update = new Update($body);

        $this->api->emitEvent(new UpdateWasReceived($update, $this->api));

        return $update;
    }
}