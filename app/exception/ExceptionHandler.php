<?php

namespace app\exception;

use Psr\Log\LoggerInterface;
use Throwable;
use Triangle\Engine\Exception\BusinessException;
use Triangle\Engine\Exception\ExceptionHandlerInterface;
use Triangle\Engine\Http\Request;
use Triangle\Engine\Http\Response;

class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * Не сообщать об исключениях этих типов
     * @var array
     */
    public array $dontReport = [BusinessException::class];

    /**
     * @var LoggerInterface|null
     */
    protected ?LoggerInterface $logger = null;

    /**
     * @var bool
     */
    protected bool $debug = true;

    /**
     * Конструктор обработчика исключений.
     * @param LoggerInterface|null $logger
     * @param bool $debug
     */
    public function __construct(?LoggerInterface $logger = null, bool $debug = true)
    {
        $this->logger = $logger;
        $this->debug = $debug;
    }

    /**
     * Отчет об исключении
     * @param Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        if ($this->shouldnt($exception, $this->dontReport)) {
            return;
        }

        $logs = '';
        if ($request = request()) {
            $logs = $request->getRealIp() . ' ' . $request->method() . ' ' . trim($request->fullUrl(), '/');
        }
        $this->logger->error($logs . PHP_EOL . $exception);
    }

    /**
     * Проверка, следует ли игнорировать исключение
     * @param Throwable $e
     * @param array $exceptions
     * @return bool
     */
    protected function shouldnt(Throwable $e, array $exceptions): bool
    {
        foreach ($exceptions as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }
        return false;
    }

    /**
     * Рендеринг исключения
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     * @throws Throwable
     */
    public function render(Request $request, Throwable $exception): Response
    {
        if ($exception instanceof BusinessException) {
            $json = [
                'status' => $exception->getCode() ?: 500,
                'error' => $exception->getMessage(),
            ];
        } else {
            $json = [
                'status' => 500,
                'error' => 'Внутренняя ошибка',
            ];

            if ($this->debug) {
                $json['debug'] = $this->debug;
                $json['data'] = $exception->getMessage();
                $json['traces'] = nl2br((string)$exception);
            }
        }

        // Ответ JSON
        if ($request->expectsJson()) return responseJson($json);

        return responseView($json, 500);
    }
}