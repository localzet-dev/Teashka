<?php

namespace app\helpers;

use Exception;
use support\Request;

class VoiceHandler
{
    private static string $model = '/resources/vosk-model-small';
    private static string $recognizer = '/resources/scripts/recognizer.py';
    private const MODEL_PATH = '/resources/vosk-model-small';
    private const RECOGNIZER_PATH = '/resources/scripts/recognizer.py';
    private const SUCCESS_PREFIX = 'suc:';
    private const ERROR_PREFIX = 'err:';

    /**
     * @param Request $request
     * @return string|null
     * @throws Exception
     */
    public static function handle(Request $request): ?string
    {
        if ($request->message->voice) {
            $voicePath = $request->telegram->downloadVoice($request->message);
            return static::recognize($voicePath);
        } else {
            return null;
        }
    }

    /**
     * Распознает голосовой файл с помощью скрипта.
     *
     * @param string $voiceFile Путь к голосовому файлу.
     * @return string Распознанный текст.
     * @throws Exception Если произошла ошибка при распознавании голоса.
     */
    public static function recognize(string $voiceFile): string
    {
        $model_path = base_path(self::MODEL_PATH);
        $recognizer_path = base_path(self::RECOGNIZER_PATH);

        // Проверяем, существуют ли файлы
        if (
            !file_exists($voiceFile)
            || !file_exists($model_path)
            || !file_exists($recognizer_path)
        ) {
            throw new Exception('Файл не найден: ' . $voiceFile);
        }

        // Формируем команду для запуска скрипта распознавания голоса
        $command = sprintf(
            'python3 %s %s %s',
            escapeshellarg($recognizer_path),
            escapeshellarg($voiceFile),
            escapeshellarg($model_path)
        );

        // Запускаем команду и получаем результат
        $result = shell_exec($command);

        // Проверяем, был ли результат
        if ($result === null) {
            throw new Exception('Ошибка распознавания голоса');
        }

        // Обрабатываем результат
        $result = trim($result);

        // Проверяем, успешно ли прошло распознавание
        if (str_starts_with($result, self::ERROR_PREFIX)) {
            throw new Exception('Ошибка распознавания голоса: ' . substr($result, strlen(self::ERROR_PREFIX)));
        }

        if (str_starts_with($result, self::SUCCESS_PREFIX)) {
            return substr($result, strlen(self::SUCCESS_PREFIX));
        }

        throw new Exception('Неизвестная ошибка распознавания голоса');
    }
}
