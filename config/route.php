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
    \support\Log::error('Teashka log is work!', ['hello' => 'world']);
    return responseJson('Ну допустим, мяу!');
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