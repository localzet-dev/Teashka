<?php

namespace app\auth\controller;

use app\model\Attempts;
use app\model\User;
use support\Request;
use support\Response;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Throwable;
use Triangle\Engine\Exception\BusinessException;

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
        $user = $request->user;
        $code = $request->code;

        if ($user->state == User::DONE) {
            throw new BusinessException("Аккаунт уже активирован!");
        }

        $attempt = $user->getAttempt();

        $login = $attempt->login;
        $hashedCode = hash_hmac('md5', $login, getenv('SECRET'));

        if (!hash_equals($hashedCode, $code)) {
            throw new BusinessException('Неверный код');
        }

        $user->delAttempt();

        foreach (Attempts::byLogin($login) as $err_attempt) {
            $err_user = User::find($err_attempt->user);
            $request->telegram->sendMessage("Пользователь ($login) привязал другой аккаунт. Ваша попытка сброшена!", $err_user->id);
            $err_user->delAttempt();

            $err_user->state(User::START);
            $request->telegram->sendMessage("Для использования бота пришли свой E-Mail (логин), привязанный к edu.donstu.ru", $err_user->id);
        }

        $user->update(['login' => $login, 'state' => User::DONE]);

        $request->telegram->sendMessage(<<<MESSAGE
        Поздравляю! Твой аккаунт активирован, теперь тебе доступны все функции :)
        Ты можешь спросить меня о парах на завтра или написать "Помощь", если что-то пойдёт не так.
        Скоро я научусь и другим функциям, следи за обновлениями: <a href="https://t.me/dstu_devs">@dstu_devs</a>
        MESSAGE, $user->id);

        return response('Аккаунт активирован. <a href="https://t.me/TeashkaBot">Вернись в телеграм</a>');
    }
}
