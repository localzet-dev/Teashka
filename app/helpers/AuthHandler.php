<?php

namespace app\helpers;

use app\model\Attempts;
use app\model\User;
use app\repositories\UniT;
use support\Request;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Triangle\Engine\Exception\BusinessException;

class AuthHandler
{
    /**
     * @param $request
     * @throws BusinessException
     */
    public static function handle($request): void
    {
        switch ($request->user->state) {
            case User::START:
                static::start($request);
            case User::PENDING:
                static::pending($request);
            case User::DONE:
                return;
        }
    }

    /**
     * @param Request $request
     * @throws BusinessException
     */
    private static function start(Request $request): void
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
        $username = static::getUsername($request);

        UniT::eduMailSend(
            "Тишка: Авторизация",
            "Привет! Твоя ссылка для авторизации: [$url]($url). Так я смогу убедиться, что Telegram-аккаунт ($username) принадлежит тебе :)\nВнимание!!! Если ты НЕ пытался войти в бота - НЕ ПЕРЕХОДИ ПО ССЫЛКЕ, это даст пользователю доступ к твоим данным!",
        );

        $request->user->state(User::PENDING);
        $request->user->save();

        throw new BusinessException(<<<MESSAGE
        Чтобы продолжить тебе нужно подтвердить свой аккаунт. 
        Я отправил ссылку для авторизации на  внутреннюю почту. Если хочешь отменить запрос - отправь /cancel. 
        Чтобы попасть на внутреннюю почту перейди по ссылке https://edu.donstu.ru/WebApp/#/mail/all
        MESSAGE
        );
    }

    /**
     * @throws BusinessException
     * @throws TelegramSDKException
     */
    public static function verify(Request $request): void
    {
        if ($request->user->state == User::DONE) {
            throw new BusinessException("Аккаунт уже активирован!");
        }

        $attempt = $request->user->getAttempt();
        $login = $attempt->login;
        $hashedCode = hash_hmac('md5', $login, getenv('SECRET'));

        if (!hash_equals($hashedCode, $request->code)) {
            throw new BusinessException('Неверный код');
        }

        $request->user->delAttempt();
        $request->user->update(['login' => $login]);
        $request->user->state(User::DONE);
        $request->user->save();

        foreach (Attempts::byLogin($login) as $err_attempt) {
            $err_user = User::find($err_attempt->user);
            $request->telegram->sendMessage("Пользователь ($login) привязал другой аккаунт. Ваша попытка сброшена!", $err_user->id);
            self::cancel($err_user);
            $request->telegram->sendMessage("Для использования бота пришли свой E-Mail (логин), привязанный к edu.donstu.ru", $err_user->id);
        }
    }

    /**
     * @param Request $request
     * @throws BusinessException
     */
    private static function pending(Request $request): void
    {
        if ($request->message->text == '/cancel') {
            static::cancel($request->user);
            throw new BusinessException("Для использования бота пришли свой E-Mail (логин), привязанный к edu.donstu.ru");
        }
        throw new BusinessException("Проверь внутреннюю почту (https://edu.donstu.ru/WebApp/#/mail/all)");
    }

    /**
     * @param User $user
     */
    private static function cancel(User $user): void
    {
        $user->delAttempt();
        $user->state(User::START);
        $user->save();
    }

    /**
     * @param Request $request
     * @return string
     */
    private static function getUsername(Request $request): string
    {
        $username = '@' . $request->message->from->username ?? $request->message->from->id;

        if (!empty($request->message->from->firstname)) {
            $username = $request->message->from->firstname;
            if (!empty($request->message->from->lastname)) {
                $username = $request->message->from->firstname . ' ' . $request->message->from->lastname;
            }
        }

        return $username;
    }
}