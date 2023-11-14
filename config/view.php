<?php

/**
 * @package     Triangle Web
 * @link        https://github.com/Triangle-org
 *
 * @copyright   2018-2023 UniT Group
 * @license     https://mit-license.org MIT
 */

use Triangle\Engine\View\Raw;

return [
    'handler' => Raw::class,
    'options' => [
        'view_suffix' => 'phtml',
        'view_global' => true,
    ]

];