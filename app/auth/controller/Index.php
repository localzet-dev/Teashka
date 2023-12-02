<?php

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
        Ты можешь спросить меня о парах на завтра или написать "Помощь", если что-то пойдёт не так.
        Скоро я научусь и другим функциям, следи за обновлениями: <a href="https://t.me/dstu_devs">@dstu_devs</a>
        MESSAGE, $request->user->id);

        return response('Аккаунт активирован. <a href="https://t.me/TeashkaBot">Вернись в телеграм</a>');
    }
}
