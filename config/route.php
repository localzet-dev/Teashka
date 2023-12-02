<?php

/**
 * @package     Triangle Web
 * @link        https://github.com/Triangle-org
 *
 * @copyright   2018-2023 Localzet Group
 * @license     https://mit-license.org MIT
 */

use support\Response;
use Triangle\Engine\Router;

Router::any('/robots.txt', function () {
    return new Response(200, [], "User-agent: *\nDisallow: /");
});