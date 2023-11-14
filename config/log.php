<?php

/**
 * @package     Triangle Web
 * @link        https://github.com/Triangle-org
 *
 * @copyright   2018-2023 Localzet Group
 * @license     https://mit-license.org MIT
 */

return [
    'default' => [
        'handlers' => [
            [
                'class' => Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/framex.log',
                    7, //$maxFiles
                    Monolog\Logger::DEBUG,
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [null, 'Y-m-d H:i:s', true],
                ],
            ]
        ],
//        [
//            'class' => Monolog\Handler\MongoDBHandler::class,
//            'constructor' => [
//                new MongoDB\Client('mongodb://'.getenv('DB_USER').':'.getenv('DB_PASS').'@'.getenv('DB_HOST').':'.getenv('DB_PORT').'/?authSource=admin&directConnection=true'),
//                getenv('DB_NAME'),
//                'Logs'
//            ],
//            'formatter' => [
//                'class' => Monolog\Formatter\MongoDBFormatter::class,
//                'constructor' => [10, false],
//            ],
//        ]
    ],
    'http' => [
        'handlers' => [
            [
                'class' => Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/http.log',
                    7, //$maxFiles
                    Monolog\Logger::DEBUG,
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [null, 'Y-m-d H:i:s', true],
                ],
            ]
        ],
    ],
];
