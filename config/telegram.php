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

return [
    'token' => getenv('TG_TOKEN'),
    'supported' => [
        'events' => ['message'],
        'types' => ['private'],
        'messages' => ['text', 'voice'],
    ],

    'ips' => [
        '91.105.192.0/23',
        '91.108.4.0/22',
        '91.108.8.0/22',
        '91.108.12.0/22',
        '91.108.16.0/22',
        '91.108.20.0/22',
        '91.108.56.0/23',
        '91.108.58.0/23',
        '95.161.64.0/20',
        '149.154.160.0/22',
        '149.154.164.0/22',
        '149.154.168.0/22',
        '149.154.172.0/22',
        '185.76.151.0/24',
        // '2001:67c:4e8::/48',
        // '2001:b28:f23c::/48',
        // '2001:b28:f23d::/48',
        // '2001:b28:f23f::/48',
        // '2a0a:f280:203::/48',
    ]
];
