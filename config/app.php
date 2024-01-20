<?php

/**
 * @package     Triangle Web
 * @link        https://github.com/Triangle-org
 *
 * @copyright   2018-2023 Localzet Group
 * @license     https://mit-license.org MIT
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
