<?php

/**
 * @package     Triangle Web
 * @link        https://github.com/Triangle-org
 *
 * @copyright   2018-2023 Localzet Group
 * @license     https://mit-license.org MIT
 */

return [
    'default' => 'Teashka',
    'connections' => [
        'Teashka' => [
            'driver' => 'mongodb',
            'host'     => getenv('DB_HOST'),
            'port'     => getenv('DB_PORT'),
            'database' => getenv('DB_NAME'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS'),
            'options' => [
                'appname' => 'Teashka',
                'authSource' => 'admin',
                'connectTimeoutMS' => 2000,
                'directConnection' => true,
            ],
        ],
    ],
];
