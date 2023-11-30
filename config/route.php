<?php

/**
 * @package     Triangle Web
 * @link        https://github.com/Triangle-org
 *
 * @copyright   2018-2023 Localzet Group
 * @license     https://mit-license.org MIT
 */

use app\service\UniT;
use support\Request;
use support\Response;
use Triangle\Engine\Router;

Router::any('/robots.txt', function (Request $request) {
    return new Response(200, [], "User-agent: *\nDisallow: /");
});

Router::any('/t', function (Request $request) {
    $array = UniT::request('db/query', ['query' => 'select top 10 * from "нирПользователиВсе"']);
    return responseJson($array);
});

Router::fallback(function () {
    return response('Привет! Меня зовут Тишка :) </br> А это главный сервер, на котором я живу');
//    return new Response(404, [], file_get_contents('/var/www/index.html'));
});
