<?php
/**
 * @package     Zorin Teashka
 * @link        https://teashka.zorin.space
 * @link        https://github.com/localzet-dev/Teashka
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2024 Zorin Projects S.P.
 * @license     https://www.gnu.org/licenses/agpl-3.0 GNU Affero General Public License v3.0
 *
 *              This program is free software: you can redistribute it and/or modify
 *              it under the terms of the GNU Affero General Public License as published
 *              by the Free Software Foundation, either version 3 of the License, or
 *              (at your option) any later version.
 *
 *              This program is distributed in the hope that it will be useful,
 *              but WITHOUT ANY WARRANTY; without even the implied warranty of
 *              MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *              GNU Affero General Public License for more details.
 *
 *              You should have received a copy of the GNU Affero General Public License
 *              along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 *              For any questions, please contact <creator@localzet.com>
 */

namespace app\repositories;

use Exception;
use support\protocols\LWP;

class UniT
{
    /**
     * Получает данные пользователя по логину из UniT.
     *
     * @param string $login Логин пользователя.
     * @return array Данные пользователя.
     * @throws Exception В случае ошибки при выполнении запроса.
     */
    public static function userByLogin(string $login, int $telegram = null): array
    {
        return self::request(
            'user/bylogin',
            [
                'login' => $login,
                'set_teashka_tg' => $telegram
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
    public static function getSchedule(int $user, int|string $start, int|string $end = null): mixed
    {
        return self::request(
            'schedule/get',
            [
                'user' => $user,
                'start' => $start,
                'end' => $end ? $end : null
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
        return LWP::request(
            getenv('UNIT_SERVER') . ltrim($uri, '/'),
            $data,
            'Teashka',
            file_get_contents(base_path(getenv('UNIT_SECURITY_ENCRYPTION'))),
            file_get_contents(base_path(getenv('UNIT_SECURITY_SIGNATURE'))),
        );
    }
}
