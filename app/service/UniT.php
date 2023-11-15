<?php

namespace app\service;

use Exception;

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
     * @param array $parameters Параметры запроса.
     * @param string $user Токен пользователя.
     * @return bool|array|string Результат запроса.
     * @throws Exception В случае ошибки при выполнении запроса.
     */
    private static function request(string $uri, array $parameters = [], string $user = ''): bool|array|string
    {
        $uri = getenv('UNIT_SERVER') . $uri;

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($parameters),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_ENCODING => 'identity',
            CURLOPT_USERAGENT => config('app.name'),
            CURLOPT_URL => $uri,
            CURLOPT_HTTPHEADER => [
                'Accept: */*',
                'Authorization: ' . $user,
                'X-API-Key: ' . '!!!',
                'Content-Type: application/json',
                'Cache-Control: max-age=0',
                'Connection: keep-alive',
                'Expect: ',
                'Pragma: ',
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        if ($response === false) {
            throw new Exception('Не могу подключиться к серверу');
        }

        $json = json_decode($response, true);

        if ($json && isset($json['status']) && $json['status'] != 200) {
            throw new Exception($json['error']);
        }

        return $json['data'] ?? $response;
    }
}
