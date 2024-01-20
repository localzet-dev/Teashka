<?php

namespace app\repositories;

use Exception;
use support\protocols\LWP;

class Cloud
{
    /**
     * Обрабатывает запрос к Cloud API, и возвращает результат.
     *
     * @param string $text
     * @param string $sid
     * @return array
     * @throws Exception
     */
    public static function detectIntent(string $text, string $sid): mixed
    {
        return self::request(
            'intent-detector',
            [
                'text' => $text,
                'sid' => $sid
            ]
        );
    }

    /**
     * Выполняет HTTP-запрос к серверу Cloud.
     *
     * @param string $uri URI запроса.
     * @param array $data Параметры запроса.
     * @return bool|array|string Результат запроса.
     * @throws Exception В случае ошибки при выполнении запроса.
     */
    protected static function request(
        string $uri,
        array  $data,
    ): bool|array|string
    {
        return LWP::request(
            getenv('CLOUD_SERVER') . ltrim($uri, '/'),
            $data,
            'Teashka',
            file_get_contents(base_path(getenv('CLOUD_SECURITY_ENCRYPTION'))),
            file_get_contents(base_path(getenv('CLOUD_SECURITY_SIGNATURE'))),
        );
    }
}
