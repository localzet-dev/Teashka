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

namespace app\helpers;

use app\model\Attempts;
use app\model\User;
use app\repositories\UniT;
use Exception;
use support\Request;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Triangle\Engine\Exception\BusinessException;

class AuthHandler
{
    /**
     * @param $request
     * @throws BusinessException
     * @throws Exception
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
     * @throws Exception
     */
    private static function start(Request $request): void
    {
        $login = trim($request->message->text);

        if (User::isRegistered($login)) {
            throw new BusinessException("Этот аккаунт уже привязан! Введи свою почту, или обратись в поддержку");
        }


        $user_id = UniT::userByLogin($login, $request->chat->id)['user_id'];
        $request->user->addAttempt($login, $user_id);

        $code = hash_hmac('md5', $login, getenv('SECRET'));
        $url = "https://" . config('app.domain') . "/auth?id=" . $request->chat->id . "&code=" . $code;
        $username = static::getUsername($request);

        UniT::eduMailSend(
            (int) $user_id,
            "Тишка: Авторизация",
            "Привет! Твоя ссылка для авторизации: [$url]($url). Так я смогу убедиться, что Telegram-аккаунт ($username) принадлежит тебе :)\nВнимание!!! Если ты НЕ пытался войти в бота - НЕ ПЕРЕХОДИ ПО ССЫЛКЕ, это даст пользователю доступ к твоим данным!",
        );

        $request->user->update(['state' => User::PENDING]);
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
        $request->user->update(['login' => $login, 'user_id' => $attempt->user_id, 'state' => User::DONE]);
        $request->user->save();

        foreach (Attempts::byLogin($login) as $err_attempt) {
            $err_user = User::find($err_attempt['user']);
            $request->telegram->sendMessage("Пользователь ($login) привязал другой аккаунт. Твоя попытка сброшена!", $err_user->id);
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
        $user->update(['state' => User::START]);
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
