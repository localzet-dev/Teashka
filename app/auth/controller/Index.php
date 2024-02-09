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

namespace app\auth\controller;

use app\helpers\AuthHandler;
use support\Request;
use support\Response;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Throwable;

class Index
{
    /**
     * Обрабатывает запрос на активацию аккаунта.
     *
     * @param Request $request Объект запроса.
     * @return Response Ответ сервера.
     * @throws TelegramSDKException
     * @throws Throwable
     */
    public function index(Request $request): Response
    {
        AuthHandler::verify($request);

        $request->telegram->sendMessage(<<<MESSAGE
        Поздравляю! Твой аккаунт активирован, теперь тебе доступны все функции :)
        Ты можешь спросить меня о парах на завтра. Если вдруг что-то пойдет не так - напиши "Помощь".
        Скоро я научусь и другим функциям, следи за обновлениями: <a href="https://t.me/dstu_devs">@dstu_devs</a>
        MESSAGE, $request->user->id);

        return response('Аккаунт активирован. <a href="https://t.me/TeashkaBot">Вернись в телеграм</a>');
    }
}
