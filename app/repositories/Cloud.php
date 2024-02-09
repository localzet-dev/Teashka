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
