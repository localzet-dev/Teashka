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

namespace app\middleware;

use app\model\User;
use app\service\Telegram;
use Exception;
use support\Log;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Objects\Chat;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;
use Throwable;
use Triangle\Engine\Exception\BusinessException;
use Triangle\Engine\Http\Request;
use Triangle\Engine\Http\Response;
use Triangle\Engine\Middleware\MiddlewareInterface;
use const PHP_EOL;

class TelegramMiddleware implements MiddlewareInterface
{
    /**
     * Обрабатывает запрос и проверяет его на соответствие требованиям Telegram.
     *
     * @param Request $request Объект запроса.
     * @param callable $handler Функция следующего промежуточного слоя.
     * @return Response Ответ сервера.
     * @throws BusinessException
     * @throws TelegramSDKException
     * @throws Throwable
     */
    public function process(Request $request, callable $handler): Response
    {
        // Проверяем, является ли запрос запросом от Telegram
        if (!$this->isTelegramRequest($request)) {
            throw new Exception("Неподдерживаемый клиент", 400);
        }

        Log::debug('Запрос от Telegram', $request->toArray());

        try {
            $request->telegram = new Telegram(config('telegram.token'));
            $request->input = $request->telegram->parseInput($request);

            $request->type = $this->getType($request->input);
            $request->chat = $this->getChat($request->input);

            try {
                $request->message = $this->getMessage($request->input);
                $request->user = $this->getUser($request);
            } catch (BusinessException $error) {
                $request->telegram->sendMessage($error->getMessage(), $request->chat->id);
                return response('ok');
            }

            /** @var Response $response */
            $response = $handler($request);
        } catch (Throwable $exception) {
            Log::error($exception->getMessage(), ['exception' => (string)$exception, 'exception_arr' => (array)$exception]);
            throw $exception;
        }

        Log::debug('Ответ для Telegram', ['body' => $response->rawBody()]);

        return $response;
    }

    /**
     * Проверяет, является ли запрос запросом от Telegram.
     *
     * @return bool Результат проверки.
     */
    private function isTelegramRequest(Request $request): bool
    {
        // TODO: сделать в конфиге отдельное поле для выбора режима: фильтрация по белому списку IP или по заголовку X-Telegram-Bot-Api-Secret-Token

//        // Получаем белый список IP-адресов Telegram из конфигурации
//        $whitelist = config('telegram.ips');
//
//        // Если белый список не определен, считаем, что запрос является запросом от Telegram
//        if ($whitelist === null) {
//            return true;
//        }
//
//        // Получаем IP-адрес запроса
//        $requestIp = getRequestIp();
//
//        // Проверяем, принадлежит ли IP-адрес запроса одному из IP-адресов Telegram в белом списке
//        foreach ($whitelist as $telegramIP) {
//            $telegramIpCidr = substr($telegramIP, 0, strpos($telegramIP, '/'));
//            if (ip2long($requestIp) & ip2long($telegramIpCidr)) {
//                return true;
//            }
//        }

        if ($request->header('X-Telegram-Bot-Api-Secret-Token') === getenv('TG_SECRET')) {
            return true;
        }

        return false;
    }

    /**
     * @throws Exception
     */
    private function getType(Update $input): ?string
    {
        // Проверяем тип объекта
        $event = $input->objectType();
        $supportedEvents = config('telegram.supported.events', []);
        if (!in_array($event, $supportedEvents)) {
            throw new Exception("Некорректный запрос", 400);
        }
        return $event;
    }

    /**
     * @throws Exception
     */
    private function getChat(Update $input): Chat
    {
        // Проверяем тип чата
        $chat = $input->getChat();
        $supportedChatTypes = config('telegram.supported.types', []);
        if (!in_array($chat->type, $supportedChatTypes)) {
            throw new Exception("Неподдерживаемый тип чата", 400);
        }
        return $chat;
    }

    /**
     * @throws Exception
     */
    private function getMessage(Update $input): Message
    {
        // Проверяем тип сообщения (должно быть текстовое или голосовое)
        $message = $input->getMessage();
        if (!($message->text || $message->voice)) {
            throw new BusinessException("Извини, я понимаю только текстовые и голосовые сообщения🥺");
        }
        return $message;
    }

    /**
     * @throws Throwable
     * @throws TelegramSDKException
     */
    private function getUser(Request $request): User|null
    {
        // Проверяем наличие пользователя в базе данных
        $user = User::find($request->chat->id);

        if (!$user) {
            // Если пользователя нет, создаем нового и отправляем приветственное сообщение
            User::create(['id' => $request->chat->id, 'state' => User::START]);
            $request->telegram->sendMessage("Привет! На связи Тишка, чат-бот помощник для студентов и преподавателей ДГТУ 🐱" . PHP_EOL .
                "Я особенный, потому что я первый бот с расписанием, который не использует шаблонные фразы и может понимать тебя, даже твои голосовые команды!", $request->chat->id);
            throw new BusinessException("Чтобы продолжить напиши свой E-Mail (логин), привязанный к edu.donstu.ru");
        }

        return $user;
    }
}
