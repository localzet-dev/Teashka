<?php

namespace app\auth\controller;

use app\model\User;
use app\service\Telegram;
use Exception;
use support\Request;
use support\Response;

class Index
{
    /**
     * Обрабатывает запрос на активацию аккаунта.
     *
     * @param Request $request Объект запроса.
     * @return Response Ответ сервера.
     * @throws Exception Если URL некорректный.
     */
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
            return response("Ошибка ID. " . Telegram::PHRASES['contact_support']);
        }

        if ($user->state == User::DONE) {
            return response(Telegram::PHRASES['account_already_activated_web']);
        }

        $hashedCode = hash_hmac('md5', $user->login, config('app.secret'));

        if ($code == $hashedCode) {
            $user->update(['state' => User::DONE]);
            Telegram::sendMessage(Telegram::PHRASES['account_activated_tg'], $id);
            return response(Telegram::PHRASES['account_activated']);
        } else {
            return response("Ошибка активации. " . Telegram::PHRASES['contact_support']);
        }
    }
}
