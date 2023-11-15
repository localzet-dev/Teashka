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
        // Проверяем, существует ли файл
        if (!file_exists($voiceFile)) {
            throw new Exception('Файл не найден: ' . $voiceFile);
        }

        // Формируем команду для запуска скрипта распознавания голоса
        $command = sprintf('python3 %s %s', base_path() . '/resources/recognizer.py', escapeshellarg($voiceFile));

        // Запускаем команду и получаем результат
        $result = shell_exec($command);

        // Проверяем, был ли результат
        if ($result === null) {
            throw new Exception('Ошибка распознавания голоса');
        }

        // Обрабатываем результат
        $result = trim($result);

        return substr($result, 4);
    }
}
