<?php

/**
 * @package     Triangle Web
 * @link        https://github.com/Triangle-org
 *
 * @copyright   2018-2023 Localzet Group
 * @license     https://mit-license.org MIT
 */

use app\middleware\TelegramMiddleware;

return [
    'api' => [TelegramMiddleware::class]
];
