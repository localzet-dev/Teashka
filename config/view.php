<?php

use support\view\Raw;

return [
    'handler' => Raw::class,
    'options' => [
        'view_suffix' => 'phtml',
        'view_global' => true,
    ]

];
