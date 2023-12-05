<?php

namespace app\repositories;

use app\helpers\LWP;
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
    public static function userByLogin(string $login, int $telegram): array
    {
        return self::request(
            'user/by-login',
            [
                'login' => $login,
                'setTelegram' => $telegram
            ]
        );
    }

    /**
     * Отправляет письмо на внутренний электронный адрес пользователя.
     *
     * @param int $user
     * @param string $theme Тема письма.
     * @param string $message Текст письма.
     * @return array|bool|string
     * @throws Exception В случае ошибки при выполнении запроса.
     */
    public static function eduMailSend(int $user, string $theme, string $message): bool|array|string
    {
        return self::request(
            'mail/self-send',
            [
                'user' => $user,
                'theme' => $theme,
                'message' => $message,
            ],
        );
    }

    /**
     * Получает расписание пользователя.
     *
     * @param int|string $start Начало периода (временная метка).
     * @param int|string $end Окончание периода (временная метка).
     * @return array Расписание пользователя.
     * @throws Exception В случае ошибки при выполнении запроса.
     */
    public static function getSchedule(int $user, int|string $start, int|string $end): array
    {
        return self::request(
            'schedule/get',
            [
                'user' => $user,
                'start' => strtotime($start),
                'end' => strtotime($end)
            ],
        );
    }

    /**
     * Выполняет HTTP-запрос к серверу UniT.
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
        return LWP::requestV3(
            getenv('UNIT_SERVER') . ltrim($uri, '/'),
            $data,
            'Teashka',
            file_get_contents(base_path(getenv('UNIT_SECURITY_ENCRYPTION'))),
            file_get_contents(base_path(getenv('UNIT_SECURITY_SIGNATURE'))),
        );
    }
}
