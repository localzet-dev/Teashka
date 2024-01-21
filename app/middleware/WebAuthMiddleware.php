<?php

namespace app\middleware;

use app\model\User;
use app\service\Telegram;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Triangle\Engine\Exception\BusinessException;
use Triangle\Engine\Http\Request;
use Triangle\Engine\Http\Response;
use Triangle\Engine\Middleware\MiddlewareInterface;

class WebAuthMiddleware implements MiddlewareInterface
{
    /**
     * @throws BusinessException
     * @throws TelegramSDKException
     */
    public function process(Request $request, callable $handler): Response
    {
        $id = $request->get('id');
        $request->code = $request->get('code');

        // Проверяем, что параметры не пустые
        if (empty($id) || empty($request->code)) {
            throw new BusinessException('Некорректный URL. Обратись за помощью к администрации <a href="https://t.me/dstu_support">@dstu_support</a>');
        }

        $request->user = User::find((int) $id);
        if (!$request->user) {
            throw new BusinessException('Ошибка ID. Обратись за помощью к администрации <a href="https://t.me/dstu_support">@dstu_support</a>');
        }

        $request->telegram = new Telegram(config('telegram.token'));

        return $handler($request);
    }
}
