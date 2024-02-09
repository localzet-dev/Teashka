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

use support\Request;

return [
    'debug' => true,
    'error_reporting' => E_ALL,
    'default_timezone' => 'Europe/Moscow',
    'request_class' => Request::class,
    'public_path' => base_path() . DIRECTORY_SEPARATOR . 'public',
    'runtime_path' => base_path(false) . DIRECTORY_SEPARATOR . 'runtime',
    'controller_suffix' => '',
    'controller_reuse' => false,

    'domain' => 'teashka.zorin.space',
    'src' => 'https://cdn.localzet.com/',

    'name' => 'Teashka',
    'description' => 'Telegram-бот помощник студента',
    'keywords' => 'localzet, localzet-dev, Triangle, Teashka, DSTU',

    'logo' => 'https://cdn.localzet.com/media/teashka/smile.png',
    'og_image' => 'https://cdn.localzet.com/media/general.svg',

    'owner' => 'Ivan Zorin <creator@localzet.com>',
    'author' => 'Ivan Zorin <ivan@zorin.space>',
    'copyright' => 'Zorin Projects S.P.',
    'reply_to' => 'support@localzet.com',

    'headers' => [
        'Content-Language' => 'ru',
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Allow-Methods' => 'OPTIONS, GET, POST',
        'Access-Control-Allow-Headers' => '*',

//        'Referrer-Policy' => 'no-referrer-when-downgrade',
//        'Content-Security-Policy' => 'default-src \'self\' http: https: ws: wss: data: blob: \'unsafe-inline\'; frame-ancestors \'self\';',
//        'Permissions-Policy' => 'interest-cohort=()',
//        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
//
//        'Alt-Svc' => 'h3=":443"; ma=2592000,h3-29=":443"; ma=2592000',
//
//        'X-Quic' => 'h3',
//        'X-Frame-Options' => 'SAMEORIGIN',
//        'X-XSS-Protection' => '1; mode=block',
//        'X-Content-Type-Options' => 'nosniff',
    ],
];
