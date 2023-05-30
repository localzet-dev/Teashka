<?php

namespace app\api\controller;

use app\model\User;
use app\service\Localzet;
use app\service\Telegram;
use support\Request;
use support\Response;

class Index
{
    /**
     * Обрабатывает запрос на главную страницу.
     *
     * @param Request $request Объект запроса.
     * @return Response Ответ сервера.
     */
    public function index(Request $request): Response
    {
        $user = $request->user;
        $message = $request->message;

        if ($user->state != User::DONE) {
            $this->auth($request);
            return response();
        }

        if ($message->voice) {
            Telegram::downloadVoice($message->voice);
        }

        Telegram::sendMessage("А дальше я пока не умею :(");
        return response();
    }

    /**
     * Авторизует пользователя и отправляет ссылку для активации аккаунта.
     *
     * @param Request $request Объект запроса.
     * @return void
     */
    public function auth(Request $request): void
    {
        $user = $request->user;
        $message = $request->message;

        switch ($user->state) {
            case User::START:
                if (!$message->isType('text')) {
                    break;
                }

                $login = trim($message->text);
                $existingUser = User::where(['login' => $login])->exists();

                if ($existingUser) {
                    Telegram::sendMessage(Telegram::PHRASES['already_exists_user'] . Telegram::PHRASES['contact_support']);
                    return;
                }

                $token = Localzet::userByLogin($login)['token'];
                $user->update(['token' => $token, 'login' => $login]);

                $code = hash_hmac('md5', $login, config('app.secret'));
                $url = "https://" . config('app.domain') . "/auth?id=" . $request->chat->id . "&code=" . $code;

                Localzet::eduMailSend(
                    Telegram::PHRASES['edumail_theme'],
                    vsprintf(Telegram::PHRASES['edumail_message'], ['url' => $url, 'name' => $message->from->firstname . ' ' . $message->from->lastname]),
                );

                $user->update(['state' => User::VERIFY]);

                Telegram::sendMessage(Telegram::PHRASES['start_verify'] . Telegram::PHRASES['check_edumail']);
                return;

            default:
                Telegram::sendMessage(Telegram::PHRASES['check_edumail']);
                return;
        }
    }
}
