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
    'src' => 'https://cdn.localzet.com/public/',

    'name' => 'Teashka',
    'description' => 'Telegram-бот помощник студента',
    'keywords' => 'localzet, localzet-dev, Triangle, Teashka, DSTU',

    'logo' => 'https://cdn.localzet.com/public/media/teashka/smile.png',
    'og_image' => 'https://cdn.localzet.com/public/media/general.svg',

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
    ],
];