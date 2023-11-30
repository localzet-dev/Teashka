<?php

/**
 * @package     Triangle Web
 * @link        https://github.com/Triangle-org
 *
 * @copyright   2018-2023 Localzet Group
 * @license     https://mit-license.org MIT
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
