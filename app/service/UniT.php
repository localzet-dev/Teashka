<?php

namespace app\service;

use Exception;
use localzet\HTTP\Client;
use localzet\LWT;
use RuntimeException;

class UniT
{
    /**
     * Получает данные пользователя по логину из UniT.
     *
     * @param string $login Логин пользователя.
     * @return array Данные пользователя.
     * @throws Exception В случае ошибки при выполнении запроса.
     */
    public static function userByLogin($login): array
    {
        return self::request('internal/auth/unit/login', ['login' => $login]);
    }

    /**
     * Отправляет письмо на внутренний электронный адрес пользователя.
     *
     * @param string $theme Тема письма.
     * @param string $message Текст письма.
     * @throws Exception В случае ошибки при выполнении запроса.
     */
    public static function eduMailSend($theme, $message): void
    {
        self::request('unit/mail/self-send', ['theme' => $theme, 'message' => $message], request()->user->token);
    }

    /**
     * Получает расписание пользователя.
     *
     * @param int|string $start Начало периода (временная метка).
     * @param int|string $end Окончание периода (временная метка).
     * @return array Расписание пользователя.
     * @throws Exception В случае ошибки при выполнении запроса.
     */
    public static function getSchedule(int|string $start, int|string $end): array
    {
        return self::request('unit/schedule', ['start' => $start, 'end' => $end], request()->user->token);
    }

    /**
     * Выполняет HTTP-запрос к серверу UniT.
     *
     * @param string $uri URI запроса.
     * @param array $data Параметры запроса.
     * @param string $authorization Токен пользователя.
     * @return bool|array|string Результат запроса.
     * @throws Exception В случае ошибки при выполнении запроса.
     */
    private static function request(
        string $uri,
        array  $data,
        string $authorization = '',
    ): bool|array|string
    {
        $uri = getenv('UNIT_SERVER') . '/' . ltrim($uri, '/');

        $token = LWT::encode(
            $data,
            file_get_contents(base_path('resources/security/unit-public.pem')),
            'ES256K',
            file_get_contents(base_path('resources/security/teashka-private.pem')),
        );
        [$header, $payload, $signature] = explode('.', $token);

        $http = new Client();

        $response = $http->request($uri, 'POST', $payload, [
            'X-ZORIN-SIGNATURE' => $signature,
            'Authorization' => 'Teashka ' . $authorization,
        ], false, [
            CURLOPT_USERAGENT => 'Teashka'
        ]);

        if ($response === false) {
            throw new Exception('Не могу подключиться к серверу');
        }

        $json = json_decode($response, true);

        if (is_array($json) && isset($json['status']) && $json['status'] != 200 && isset($json['error'])) {
            throw new RuntimeException($json['error']);
        }

        return $json['data'] ?? $response;
    }

}
