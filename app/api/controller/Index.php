<?php

namespace app\api\controller;

use app\actions\Schedule;
use app\actions\Settings;
use app\actions\Support;
use app\helpers\Voice;
use app\model\User;
use app\repositories\Dialogflow;
use app\service\AuthService;
use app\service\Telegram;
use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use support\Request;
use support\Response;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Throwable;

class Index
{
    /**
     * Обрабатывает запрос на главную страницу.
     *
     * @param Request $request Объект запроса.
     * @return Response Ответ сервера.
     * @throws Throwable
     */
    public function index(Request $request): Response
    {
        $user = $request->user;
        $message = $request->message;
        $text = $message->text;
        $chatId = $message->chat->id;

        if ($user->state === User::START) {
            AuthService::start($request);
        } elseif ($user->state === User::VERIFY) {
            AuthService::pending($request);
        }

        // Загружаем голосовое сообщение из Telegram и распознаем его
        if ($message->voice) {
            $voicePath = Telegram::downloadVoice($message);
            $text = Voice::recognize($voicePath);
            unlink($voicePath);
        }

        if (str_starts_with($text, '/')) {
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
     * @throws TelegramSDKException
     */
    private function handleCommand(string $command, int $chatId): void
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
     * @throws ApiException
     * @throws ValidationException
     * @throws TelegramSDKException
     */
    private function handleTextMessage(string $text, int $chatId): void
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
}
