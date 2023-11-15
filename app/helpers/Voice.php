<?php

namespace app\helpers;

use Exception;

class Voice
{
    private static string $model = '/resources/vosk-model-small';
    private static string $recognizer = '/resources/scripts/recognizer.py';

    /**
     * Распознает голосовой файл с помощью скрипта.
     *
     * @param string $voiceFile Путь к голосовому файлу.
     * @return string Распознанный текст.
     * @throws Exception Если произошла ошибка при распознавании голоса.
     */
    public static function recognize(string $voiceFile): string
    {
        // Проверяем, существуют ли файлы
        if (!file_exists($voiceFile) || !file_exists(base_path(self::$model)) || !file_exists(base_path(self::$recognizer))) {
            throw new Exception('Файл не найден: ' . $voiceFile);
        }

        // Формируем команду для запуска скрипта распознавания голоса
        $command = sprintf('python3 %s %s %s', escapeshellarg(base_path(self::$recognizer)), escapeshellarg($voiceFile), escapeshellarg(base_path(self::$model)));

        // Запускаем команду и получаем результат
        $result = shell_exec($command);

        // Проверяем, был ли результат
        if ($result === null) {
            throw new Exception('Ошибка распознавания голоса');
        }

        // Обрабатываем результат
        $result = trim($result);

        // Проверяем, успешно ли прошло распознавание
        if (!str_starts_with($result, 'suc:')) {
            throw new Exception('Ошибка распознавания голоса: ' . $result);
        }

        return substr($result, 4);
    }
}
