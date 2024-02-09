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
    'default' => [
        'handlers' => [
            [
                'class' => Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/framex.log',
                    7, //$maxFiles
                    Monolog\Logger::WARNING,
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [null, 'Y-m-d H:i:s', true],
                ],
            ],
            [
                'class' => \localzet\Utils\Log\ZorinCloudLogger::class,
                'constructor' => [
                    getenv('CLOUD_AGENT'),
                    getenv('CLOUD_SERVER'),
                    file_get_contents(base_path(getenv('CLOUD_SECURITY_ENCRYPTION'))),
                    file_get_contents(base_path(getenv('CLOUD_SECURITY_SIGNATURE'))),
                    Monolog\Logger::DEBUG,
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
];
