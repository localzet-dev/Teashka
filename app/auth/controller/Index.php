<?php

namespace app\auth\controller;

use app\model\Attempts;
use app\model\User;
use app\service\Telegram;
use Exception;
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
        // Получаем параметры из запроса
        $id = $request->get('id');
        $code = $request->get('code');

        // Проверяем, что параметры не пустые
        if (empty($id) || empty($code)) {
            throw new Exception("Некорректный URL");
        }

        // Приводим ID к целочисленному значению
        $id = (int) $id;

        // Находим пользователя по ID
        $user = User::find($id);

        // Если пользователя нет, возвращаем ошибку
        if (!$user) {
            return response('Ошибка ID. Обратитесь к администратору <a href="https://t.me/GeneralRust">@GeneralRust</a>');
        }

        // Проверяем, что аккаунт пользователя еще не активирован
        if ($user->state == User::DONE) {
            return response("Аккаунт уже активирован!");
        }

        // Получаем список попыток активации для данного пользователя
        $users = Attempts::byUser($id);

        // Проверяем код активации для каждой попытки
        foreach ($users as $userlogin) {
            $login = $userlogin->login;
            $hashedCode = hash_hmac('md5', $login, getenv('SECRET'));

            // Если код активации совпадает, выполняем активацию аккаунта
            if ($code == $hashedCode) {
                // Удаляем все попытки активации для данного пользователя
                $user->delAttempt();

                // Получаем список неактивированных аккаунтов с таким же логином
                $nonacts = Attempts::byLogin($login);

                // Для каждого неактивированного аккаунта отправляем уведомление и сбрасываем попытки активации
                foreach ($nonacts as $nonact) {
                    $nonuser = User::find($nonact->user);
                    Telegram::sendMessage("Пользователь ($login) привязал другой аккаунт. Ваша попытка сброшена!", $nonuser->id);
                    $nonuser->delAttempt();

                    $nonuser->update(['state' => User::START]);
                    Telegram::sendMessage("Для использования бота пришли свой E-Mail (логин), привязанный к edu.donstu.ru");
                }

                // Обновляем данные аккаунта пользователя
                $user->update(['login' => $login, 'state' => User::DONE]);

                // Отправляем уведомление об успешной активации аккаунта
                Telegram::sendMessage(<<<MESSAGE
                Поздравляю! Твой аккаунт активирован, теперь тебе доступны все функции :)
                Ты можешь спросить меня о парах на завтра или написать \"Помощь\", если что-то пойдёт не так.
                Скоро я научусь и другим функциям, следи за обновлениями: <a href=\"https://t.me/dstu_devs\">@dstu_devs</a>
                MESSAGE, $id);

                return response('Аккаунт активирован. <a href="https://t.me/TeashkaBot">Вернись в телеграм</a>');
            }
        }

        return response('Ошибка активации. Обратитесь к администратору <a href="https://t.me/GeneralRust">@GeneralRust</a>');
    }
}
