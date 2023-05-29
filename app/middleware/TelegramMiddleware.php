<?php

namespace app\middleware;

use app\service\Telegram;
use Triangle\Engine\MiddlewareInterface;
use Triangle\Engine\Http\Response;
use Triangle\Engine\Http\Request;
use Triangle\Engine\Route;

class TelegramMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        if ($this->isTelegramRequest()) {
            $input = Telegram::parseInput($request);
            if (!in_array($input->objectType(), config('telegram.supported.events', []))) {
                return $this->getError();
            }

            $chat = $input->getChat();
            if (!in_array($chat->objectType(), config('telegram.supported.types', []))) {
                return $this->getError();
            }

            $message = $input->getMessage();
            if (!in_array($message->objectType(), config('telegram.supported.messages', []))) {
                return $this->getError();
            }

            return $this->getSuccess($request, $next);
        }

        return $this->getError();
    }

    private function isTelegramRequest()
    {
        $whitelist = config('telegram.ips');

        if ($whitelist == null) return true;

        foreach ($whitelist as $telegramIP) {
            if (ip2long(getRequestIp()) & ip2long(substr($telegramIP, 0, strpos($telegramIP, '/')))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Response
     */
    private function getError()
    {
        return Route::getFallback();
    }

    private function getSuccess(Request $request, callable $next)
    {
        return $next($request);
    }
}
