<?php

namespace app\middleware;

use app\model\User;
use Triangle\Engine\Exception\BusinessException;
use Triangle\Engine\Http\Request;
use Triangle\Engine\Http\Response;
use Triangle\Engine\Middleware\MiddlewareInterface;

class WebAuthMiddleware implements MiddlewareInterface
{
    /**
     * @throws BusinessException
     */
    public function process(Request $request, callable $handler): Response
    {
        $id = $request->get('id');
        $request->code = $request->get('code');

        // Проверяем, что параметры не пустые
        if (empty($id) || empty($request->code)) {
            throw new BusinessException('Некорректный URL. Обратитесь к администрации <a href="https://t.me/dstu_support">@dstu_support</a>');
        }

        $request->user = User::find($id);
        if (!$request->user) {
            throw new BusinessException('Ошибка ID. Обратитесь к администрации <a href="https://t.me/dstu_support">@dstu_support</a>');
        }

        return $handler($request);
    }
}