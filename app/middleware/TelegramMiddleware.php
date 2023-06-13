<?php

namespace app\middleware;

use app\model\User;
use app\service\Telegram;
use Exception;
use support\exception\BusinessException;
use Triangle\Engine\MiddlewareInterface;
use Triangle\Engine\Http\Response;
use Triangle\Engine\Http\Request;

class TelegramMiddleware implements MiddlewareInterface
{
    /**
     * Обрабатывает запрос и проверяет его на соответствие требованиям Telegram.
     *
     * @param Request $request Объект запроса.
     * @param callable $next Функция следующего промежуточного слоя.
     * @return Response Ответ сервера.
     * @throws Exception В случае некорректного запроса или неподдерживаемого клиента.
     */
    public function process(Request $request, callable $next): Response
    {
        // Проверяем, является ли запрос запросом от Telegram
        if (!$this->isTelegramRequest()) {
            throw new Exception("Неподдерживаемый клиент", 400);
        }

        // Разбираем входные данные запроса
        $input = Telegram::parseInput($request);

        // Проверяем тип объекта
        $objectType = $input->objectType();
        $supportedEvents = config('telegram.supported.events', []);
        if (!in_array($objectType, $supportedEvents)) {
            throw new BusinessException("Некорректный запрос", 400);
        }

        // Проверяем тип чата
        $chat = $input->getChat();
        $supportedChatTypes = config('telegram.supported.types', []);
        if (!in_array($chat->type, $supportedChatTypes)) {
            throw new Exception("Неподдерживаемый тип чата", 400);
        }

        // Проверяем тип сообщения (должно быть текстовое или голосовое)
        $message = $input->getMessage();
        if (!($message->text || $message->voice)) {
            Telegram::sendMessage("Извини, я понимаю только текст и голосовые 🥺", $chat->id);
            throw new Exception("Неподдерживаемый тип сообщения", 400);
        }

        // Сохраняем необходимые данные в объекте запроса
        $request->chat = $chat;
        $request->input = $input;
        $request->message = $message;

        // Проверяем наличие пользователя в базе данных
        $user = User::find($chat->id);

        if (!$user) {
            // Если пользователя нет, создаем нового и отправляем приветственное сообщение
            User::create(['id' => $chat->id, 'state' => User::START]);
            Telegram::sendMessage("Привет! На связи Тишка, чат-бот помощник для студентов и преподавателей ДГТУ 🐱" . \PHP_EOL .
                "Я первый бот с расписанием, который не использует шаблонные фразы, а понимает тебя. В том числе и голосовые сообщения!");
            Telegram::sendMessage("Чтобы продолжить напиши свой E-Mail (логин), привязанный к edu.donstu.ru");
            return response();
        }

        $request->user = $user;

        return $next($request);
    }

    /**
     * Проверяет, является ли запрос запросом от Telegram.
     *
     * @return bool Результат проверки.
     */
    private function isTelegramRequest(): bool
    {
        // TODO: сделать в конфиге отдельное поле для выбора режима: фильтрация по белому списку IP или по заголовку X-Telegram-Bot-Api-Secret-Token

        // Получаем белый список IP-адресов Telegram из конфигурации
        $whitelist = config('telegram.ips');

        // Если белый список не определен, считаем, что запрос является запросом от Telegram
        if ($whitelist === null) {
            return true;
        }

        // Получаем IP-адрес запроса
        $requestIp = getRequestIp();

        // Проверяем, принадлежит ли IP-адрес запроса одному из IP-адресов Telegram в белом списке
        foreach ($whitelist as $telegramIP) {
            $telegramIpCidr = substr($telegramIP, 0, strpos($telegramIP, '/'));
            if (ip2long($requestIp) & ip2long($telegramIpCidr)) {
                return true;
            }
        }

        return false;
    }
}
