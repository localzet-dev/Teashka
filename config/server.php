<?php

/**
 * @package     Triangle Web
 * @link        https://github.com/Triangle-org
 *
 * @copyright   2018-2023 Localzet Group
 * @license     https://mit-license.org MIT
 */

return [
    'listen' => 'http://127.0.0.7:88',
    'transport' => 'tcp',
    'context' => [],
    'name' => 'Triangle',
    'count' => cpu_count() * 4,
    'user' => '',
    'group' => '',
    'reusePort' => false,
    'event_loop' => '',
    'stop_timeout' => 2,
    'pid_file' => runtime_path() . '/triangle.pid',
    'status_file' => runtime_path() . '/triangle.status',
    'stdout_file' => runtime_path() . '/logs/stdout.log',
    'log_file' => runtime_path() . '/logs/server.log',
    'max_package_size' => 10 * 1024 * 1024
];
