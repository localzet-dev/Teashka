<?php

namespace app\api\controller;

use app\actions\Schedule;
use app\actions\Settings;
use app\actions\Support;
use app\model\Attempts;
use app\model\User;
use app\service\Dialogflow;
use app\service\Localzet;
use app\service\Telegram;
use app\service\Voice;
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
        $text = $message->text;
        $chatId = $message->chat->id;

        // Если пользователь не находится в состоянии User::DONE, перенаправляем на авторизацию
        if ($user->state != User::DONE) {
            $this->auth($request);
            return response();
        }

        // Загружаем голосовое сообщение из Telegram и распознаем его
        if ($message->voice) {
            $voicePath = Telegram::downloadVoice($message);
            $text = Voice::recognize($voicePath);
            unlink($voicePath);
        }

        if (strpos($text, '/') === 0) {
            // Обработка команды
            $this->handleCommand($text, $chatId);
        } else {
            // Обработка текстового сообщения
            $this->handleTextMessage($text, $chatId);
        }

        return response();
    }

    /**
     * Обрабатывает команду пользователя.
     *
     * @param string $command Команда пользователя.
     * @param int $chatId Идентификатор чата.
     * @return void
     */
    private function handleCommand($command, $chatId)
    {
        switch ($command) {
            case '/schedule':
                Schedule::process($chatId);
                break;
                // case '/setings':
                //     Settings::process($chatId);
                //     break;
                // case '/support':
                //     Support::process($chatId);
                //     break;
            default:
                Telegram::sendMessage('Неверная команда. Попробуйте еще раз.');
        }
    }

    /**
     * Обрабатывает текстовое сообщение пользователя.
     *
     * @param string $text Текстовое сообщение пользователя.
     * @param int $chatId Идентификатор чата.
     * @return void
     */
    private function handleTextMessage($text, $chatId)
    {
        switch ($text) {
            case 'Что ты умеешь?':
                Telegram::sendMessage('По всем вопросам обращайся к @GeneralRust :)');
                return;
            case 'Помощь':
                Telegram::sendMessage('По всем вопросам обращайся к @GeneralRust :)');
                return;
            case 'Скоро':
                Telegram::sendMessage('Скоро я получу обновление и смогу помочь тебе ещё лучше! А пока я могу показать твоё расписание)');
                return;
        }

        // Обработка текстового сообщения с помощью Dialogflow
        $result = Dialogflow::process($chatId, $text);
        $name = $result->getIntent()->getDisplayName();
        $parameters = json_decode($result->getParameters()->serializeToJsonString(), true);

        switch ($name) {
            case 'Schedule':
                Schedule::process($chatId, $parameters);
                break;
            case 'Settings':
                Settings::process($chatId);
                break;
            case 'Support':
                Support::process($chatId);
                break;
            default:
                Telegram::sendMessage('Извини, не совсем понимаю, о чём ты');
        }
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
                $login = trim($message->text);
                $existingUser = User::where(['login' => $login])->exists();

                if ($existingUser) {
                    Telegram::sendMessage("Этот аккаунт уже привязан! Введи свою почту (логин)");
                    return;
                }

                $token = Localzet::userByLogin($login)['token'];
                $user->update(['token' => $token]);
                Attempts::updateOrCreate(['user' => $user->id], ['login' => $login]);

                $code = hash_hmac('md5', $login, config('app.secret'));
                $url = "https://" . config('app.domain') . "/auth?id=" . $request->chat->id . "&code=" . $code;
                $username = '@' . $message->from->username ?? $message->from->id;

                if (!empty($message->from->firstname)) {
                    $username = $message->from->firstname;
                    if (!empty($message->from->lastname)) {
                        $username = $message->from->firstname . ' ' . $message->from->lastname;
                    }
                }

                Localzet::eduMailSend(
                    "Тишка: Авторизация",
                    "Привет! Твоя ссылка для авторизации: [$url]($url). Так я смогу убедиться, что Telegram-аккаунт ($username) принадлежит тебе :)\nВнимание!!! Если ты НЕ пытался войти в бота - НЕ ПЕРЕХОДИ ПО ССЫЛКЕ, это даст пользователю доступ к твоим данным!",
                );

                $user->update(['state' => User::VERIFY]);

                Telegram::sendMessage("Чтобы продолжить тебе нужно подтвердить свой аккаунт. " . \PHP_EOL .
                "Я отправил ссылку для авторизации на  внутреннюю почту. Если хочешь отменить запрос - отправь /cancel. " . \PHP_EOL .
                "Чтобы попасть на внутреннюю почту перейди по ссылке https://edu.donstu.ru/WebApp/#/mail/all");
                return;
                default:
                if ($message->text == '/cancel') {
                    Attempts::where('user', $user->id)->delete();
                    $user->update(['state' => User::START]);
                    Telegram::sendMessage("Для использования бота пришли свой E-Mail (логин), привязанный к edu.donstu.ru");
                }
                Telegram::sendMessage("Проверь внутреннюю почту (https://edu.donstu.ru/WebApp/#/mail/all)");
                return;
        }
    }
}
