<?php

namespace app\service;

use Exception;

class Localzet
{
    /**
     * Получает данные пользователя по логину из Localzet.
     *
     * @param string $login Логин пользователя.
     *
     * @return array Данные пользователя.
     *
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
     *
     * @throws Exception В случае ошибки при выполнении запроса.
     */
    public static function eduMailSend($theme, $message): void
    {
        self::request('unit/mail/self-send', ['theme' => $theme, 'message' => $message], request()->user->token);
    }

    /**
     * Выполняет HTTP-запрос к серверу Localzet.
     *
     * @param string $uri URI запроса.
     * @param array $parameters Параметры запроса.
     * @param string $user Токен пользователя.
     *
     * @return bool|array|string Результат запроса.
     *
     * @throws Exception В случае ошибки при выполнении запроса.
     */
    private static function request(string $uri, array $parameters = [], string $user = ''): bool|array|string
    {
        $uri = config('app.api_server', 'https://api.localzet.com/') . $uri;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($parameters));

        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($curl, CURLOPT_MAXREDIRS, 5);

        curl_setopt($curl, CURLINFO_HEADER_OUT, true);

        curl_setopt($curl, CURLOPT_ENCODING, 'identity');
        curl_setopt($curl, CURLOPT_USERAGENT, config('app.name'));

        curl_setopt($curl, CURLOPT_URL, $uri);

        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Accept: */*',
            'Authorization: ' . $user,
            'X-API-Key: ' . config('app.key', ''),
            'Content-Type: application/json',
            'Cache-Control: max-age=0',
            'Connection: keep-alive',
            'Expect: ',
            'Pragma: ',
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        if (false === $response) {
            Telegram::sendMessage('Не могу подключиться к серверу');
            throw new Exception('Не могу подключиться к серверу');
        }

        $json = @json_decode($response, true);

        if ($json && isset($json['status']) && $json['status'] != 200) {
            Telegram::sendMessage($json['error']);
            throw new Exception($json['error']);
        }

        return $json['data'] ?? $response;
    }
}
