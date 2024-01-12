<?php

/**
 * @package     Triangle Web
 * @link        https://github.com/Triangle-org
 *
 * @copyright   2018-2023 Localzet Group
 * @license     https://mit-license.org MIT
 */

use app\repositories\Cloud;
use support\Response;
use Triangle\Engine\Router;

Router::any('/robots.txt', function () {
    return new Response(200, [], "User-agent: *\nDisallow: /");
});


Router::any('/headers.json', function () {
    return responseJson(request()->header());
});

Router::any('/df', function (\support\Request $request) {
    $response = \app\repositories\Cloud::detectIntent($request->input('text'), uniqid());

    return responseJson($response);
});

Router::any('/rasp', function (\support\Request $request) {
    $response = \app\repositories\UniT::getSchedule(-289081, date('d.m.Y'));

    return responseJson($response);
});

Router::any('/log', function (\support\Request $request) {
    return responseJson(Cloud::log('DEBUG', 'Teashka Test'));
});

Router::any('/whurl', function () {
    $http = new \localzet\HTTP\Client();
    $response = $http->request(
        'https://api.telegram.org/bot' . getenv('TG_TOKEN') .'/setWebhook',
        'GET',
        [
            'url' => 'https://' . config('app.domain') . '/api',
            'max_connections' => 100,
            'allowed_updates' => serialize(config('telegram.supported.events')),
            'secret_token' => getenv('TG_SECRET')
        ]
    );

   return responseJson($response);
});

Router::any('/getwh', function () {
    $http = new \localzet\HTTP\Client();
    $response = $http->request(
        'https://api.telegram.org/bot' . getenv('TG_TOKEN') .'/getWebhookInfo',
    );

    return responseJson(json_decode($response, true));
});

Router::any('/', function (\support\Request $request) {
    return response('Система работает в штатном режиме');
});