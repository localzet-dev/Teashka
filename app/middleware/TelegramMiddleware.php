<?php

namespace app\middleware;

use app\model\User;
use app\service\Telegram;
use Exception;
use Triangle\Engine\MiddlewareInterface;
use Triangle\Engine\Http\Response;
use Triangle\Engine\Http\Request;

class TelegramMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        if (!$this->isTelegramRequest()) {
            throw new Exception("Неподдерживаемый клиент", 400);
        }

        $input = Telegram::parseInput($request);

        $objectType = $input->objectType();
        $supportedEvents = config('telegram.supported.events', []);
        if (!in_array($objectType, $supportedEvents)) {
            throw new Exception("Некорректный запрос", 400);
        }

        $chat = $input->getChat();
        $supportedChatTypes = config('telegram.supported.types', []);
        if (!in_array($chat->type, $supportedChatTypes)) {
            throw new Exception("Неподдерживаемый тип чата", 400);
        }

        $message = $input->getMessage();
        if (!($message->text || $message->voice)) {
            throw new Exception("Неподдерживаемый тип сообщения", 400);
        }

        $request->chat = $chat;
        $request->input = $input;
        $request->message = $message;

        $user = User::find($chat->id);

        if (!$user) {
            User::create(['id' => $chat->id, 'state' => User::START]);
            Telegram::sendMessage(Telegram::PHRASES['welcome']);
            return response();
        }

        $request->user = $user;

        return $next($request);
    }

    private function isTelegramRequest()
    {
        // TODO: сделать в конфиге отдельное поле для выбора режима: фильтрация по белому списку IP или по заголовку X-Telegram-Bot-Api-Secret-Token
        $whitelist = config('telegram.ips');

        if ($whitelist === null) {
            return true;
        }

        $requestIp = getRequestIp();
        foreach ($whitelist as $telegramIP) {
            $telegramIpCidr = substr($telegramIP, 0, strpos($telegramIP, '/'));
            if (ip2long($requestIp) & ip2long($telegramIpCidr)) {
                return true;
            }
        }

        return false;
    }
}
