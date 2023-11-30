<?php

namespace app\service;

use app\model\User;
use support\Request;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Triangle\Engine\Exception\BusinessException;

class AuthService
{
    /**
     * @throws TelegramSDKException
     * @throws BusinessException
     */
    public static function start(Request $request): void
    {
        $login = trim($request->message->text);

        if (User::isRegistered($login)) {
            throw new BusinessException("Этот аккаунт уже привязан! Введи свою почту, или обратитесь в поддержку");
        }

        $request->user->addAttempt($login);

        // ----------------------------------------------
        $token = UniT::userByLogin($login)['token'];
        $request->user->update(['token' => $token]);

        $code = hash_hmac('md5', $login, getenv('SECRET'));
        $url = "https://" . config('app.domain') . "/auth?id=" . $request->chat->id . "&code=" . $code;
        $username = '@' . $request->message->from->username ?? $request->message->from->id;

        if (!empty($request->message->from->firstname)) {
            $username = $request->message->from->firstname;
            if (!empty($request->message->from->lastname)) {
                $username = $request->message->from->firstname . ' ' . $request->message->from->lastname;
            }
        }

        UniT::eduMailSend(
            "Тишка: Авторизация",
            "Привет! Твоя ссылка для авторизации: [$url]($url). Так я смогу убедиться, что Telegram-аккаунт ($username) принадлежит тебе :)\nВнимание!!! Если ты НЕ пытался войти в бота - НЕ ПЕРЕХОДИ ПО ССЫЛКЕ, это даст пользователю доступ к твоим данным!",
        );

        $request->user->state(User::VERIFY);

        throw new BusinessException(<<<MESSAGE
        Чтобы продолжить тебе нужно подтвердить свой аккаунт. 
        Я отправил ссылку для авторизации на  внутреннюю почту. Если хочешь отменить запрос - отправь /cancel. 
        Чтобы попасть на внутреннюю почту перейди по ссылке https://edu.donstu.ru/WebApp/#/mail/all
        MESSAGE);

    }

    /**
     * @throws BusinessException
     */
    public static function pending(Request $request)
    {
        if ($request->message->text == '/cancel') {
            static::cancel($request);
        }
        throw new BusinessException("Проверь внутреннюю почту (https://edu.donstu.ru/WebApp/#/mail/all)");
    }

    /**
     * @throws BusinessException
     */
    public static function cancel(Request $request) {
        $request->user->delAttempt();
        $request->user->state(User::START);
        throw new BusinessException("Для использования бота пришли свой E-Mail (логин), привязанный к edu.donstu.ru");
    }
}