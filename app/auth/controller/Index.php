<?php

namespace app\auth\controller;

use app\model\User;
use app\service\Telegram;
use Exception;
use support\exception\BusinessException;
use support\Request;
use support\Response;

class Index
{
    public function index(Request $request): Response
    {
        $id = $request->get('id');
        $code = $request->get('code');

        if (empty($id) || empty($code)) {
            throw new Exception("Некорректный URL");
        }

        $id = (int) $id;
        $user = User::find($id);

        if (!$user) {
            return response("Ошибка ID. Обратитесь к администратору <a href=\"https://generalrust.t.me\">@GeneralRust</a> $id");
        }

        $hashedCode = hash_hmac('md5', $user->login, config('app.secret'));

        if ($code == $hashedCode) {
            $user->update(['state' => 'done']);
            Telegram::sendMessage('Поздравляю! Аккаунт активирован, теперь тебе доступны все функции :)', $id);
            return response("Аккаунт активирован. <a href=\"https://dstustudentbot.t.me\">Вернитесь в телеграм</a>");
        } else {
            return response("Ошибка активации. Обратитесь к администратору <a href=\"https://generalrust.t.me\">@GeneralRust</a>");
        }
    }
}
