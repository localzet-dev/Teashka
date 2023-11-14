<?php

namespace app\service;

class Voice
{
 /**
     * Распознает голосовой файл с помощью скрипта.
     *
     * @param string $voiceFile Путь к голосовому файлу.
     * @return string Распознанный текст.
     */
    public static function recognize(string $voiceFile): string
    {
        // Формируем команду для запуска скрипта распознавания голоса
        $command = sprintf('python3 %s %s', base_path() . '/resources/recognizer.py', escapeshellarg($voiceFile));

        // Запускаем команду и получаем результат
        $result = shell_exec($command);

        // Обрабатываем результат
        $result = trim($result);

        return substr($result, 4);
    }
}
