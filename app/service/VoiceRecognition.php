<?php

namespace app\service;

class VoiceRecognition
{
    public static function recognize(string $voiceFile): string
    {
        $command = sprintf('python %s %s', base_path() . '/resources/recognizer.py', escapeshellarg($voiceFile));
        $result = shell_exec($command);
        return trim($result);
    }
}
