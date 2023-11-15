<?php

namespace app\service;

use Exception;

class Voice
{
    /**
     * Распознает голосовой файл с помощью скрипта.
     *
     * @param string $voiceFile Путь к голосовому файлу.
     * @return string Распознанный текст.
     * @throws Exception Если произошла ошибка при распознавании голоса.
     */
    public static function recognize(string $voiceFile): string
    {
        // Путь к модели
        $modelPath = base_path() . '/resources/vosk-model-small';

        // Проверяем, существуют ли файлы
        if (!file_exists($voiceFile) || !file_exists($modelPath)) {
            throw new Exception('Файл не найден: ' . $voiceFile);
        }

        // Формируем команду для запуска скрипта распознавания голоса
        $command = sprintf('python3 %s %s %s', base_path() . '/resources/recognizer.py', escapeshellarg($voiceFile), escapeshellarg($modelPath));

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
