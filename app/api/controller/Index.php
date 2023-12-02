<?php

namespace app\api\controller;

use app\helpers\AuthHandler;
use app\helpers\VoiceHandler;
use app\repositories\Dialogflow;
use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use support\Request;
use support\Response;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Throwable;
use Triangle\Engine\Exception\BusinessException;

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
        // Проверяем состояние пользователя
        try {
            AuthHandler::handle($request);

            // Получаем/распознаём текст
            $text = VoiceHandler::handle($request);
            $text = $text !== null ? $text : $request->message->text;

            $this->handleTextMessage($text, $request);

            if (str_starts_with($text, '/')) {
                $this->handleCommand($text, $request);
            } else {
                $this->handleIntent($text, $request);
            }

        } catch (BusinessException $exception) {
            $request->telegram->sendMessage($exception->getMessage(), $request->chat->id);
        } catch (Throwable) {
            $request->telegram->sendMessage('Внутренняя ошибка. Пожалуйста, сообщите администрации <a href="https://t.me/dstu_support">@dstu_support</a>', $request->chat->id);
        } finally {
            return response('ok');
        }
    }

    /**
     * @param string $text
     * @param Request $request
     * @throws BusinessException
     */
    private function handleTextMessage(string $text, Request $request): void
    {
        switch ($text) {
            case 'Квиз':
                throw new BusinessException('Сейчас квиз ещё в разработке :)');
            case 'Помощь':
                throw new BusinessException('По всем вопросам обращайтесь к администрации <a href="https://t.me/dstu_support">@dstu_support</a> :)');
        }
    }

    /**
     * Обрабатывает команду пользователя.
     *
     * @param string $command Команда пользователя.
     * @param Request $request
     * @return void
     * @throws TelegramSDKException
     * @throws BusinessException
     */

    private function handleCommand(string $command, Request $request): void
    {
        // Убираем первый символ
        $command = substr($command, 1);
        $arguments = [];

        // Если у команды есть аргументы - получаем их
        if (str_contains($command, ' ')) {
            $arr = explode(' ', $command);
            $command = $arr[0];
            $arguments = array_slice($arr, 1);
        }

        // Формируем полное имя класса
        $className = '\\app\\actions\\' . ucfirst(strtolower($command));

        // Проверяем существование класса и вызываем метод handleCommand
        if (class_exists($className) && method_exists($className, 'handleCommand')) {
            call_user_func([$className, 'handleCommand'], $arguments);
        } else {
            throw new BusinessException('Неверная команда. Попробуйте еще раз.');
        }
    }

    /**
     * Обрабатывает текстовое сообщение пользователя.
     *
     * @param string $text Текстовое сообщение пользователя.
     * @param Request $request
     * @return void
     * @throws TelegramSDKException
     * @throws BusinessException
     * @throws ValidationException
     * @throws ApiException
     */
    private function handleIntent(string $text, Request $request): void
    {
        // Обработка текстового сообщения с помощью Dialogflow
        $dialogflow = new Dialogflow((string)$request->chat->id);
        $result = $dialogflow->detectIntent($text);
        $name = $result->getIntent()->getDisplayName();
        $parameters = json_decode($result->getParameters()->serializeToJsonString(), true);

        // Формируем полное имя класса
        $className = '\\app\\actions\\' . ucfirst(strtolower($name));

        // Проверяем существование класса и вызываем метод handleIntent
        if (class_exists($className) && method_exists($className, 'handleIntent')) {
            call_user_func([$className, 'handleIntent'], $parameters);
        } else {
            throw new BusinessException('Извини, не совсем понимаю, о чём ты');
        }
    }
}
