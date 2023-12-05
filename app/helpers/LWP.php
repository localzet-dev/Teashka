<?php

namespace app\helpers;

use Exception;
use localzet\LWT;
use localzet\JWT;
use localzet\HTTP\Client;
use RuntimeException;

/**
 * Класс Localzet Web Protect
 * Этот класс предназначен для обработки запросов к серверу с использованием токенов безопасности.
 */
final class LWP
{
    /**
     * Метод для отправки запроса на сервер с использованием токена безопасности.
     *
     * @param string $url URL-адрес сервера.
     * @param mixed $data Данные, которые нужно отправить на сервер.
     * @param string $agent Идентификатор пользователя.
     * @param string $key Ключ генерации JWT
     * @return string|array Возвращает данные, полученные от сервера, или полный ответ сервера, если данные отсутствуют.
     * @throws Exception Если не удается подключиться к серверу.
     */
    final public static function requestV1(
        string $url,
        mixed  $data,
        string $agent,
        string $key,
    ): string|array
    {
        // Создание токена безопасности с использованием данных и ключей безопасности.
        $token = JWT::encode(
            $data,
            $key,
            'HS256',
        );

        // Разделение токена на составляющие.
        [, $payload, $signature] = explode('.', $token);

        // Создание нового HTTP-клиента.
        $http = new Client();

        // Отправка запроса на сервер.
        $response = $http->request(
            $url,
            'POST',
            ['data' => $payload],
            ['X-API-SIGNATURE' => "JWT $signature"],
            false,
            [CURLOPT_USERAGENT => $agent]
        );

        // Проверка успешности запроса.
        if ($response === false) {
            throw new Exception('Не могу подключиться к серверу: ' . $http->getResponseClientError());
        }

        // Декодирование ответа сервера.
        $json = json_decode($response, true);

        // Проверка статуса ответа сервера.
        if (is_array($json) && isset($json['status']) && $json['status'] != 200 && isset($json['error'])) {
            throw new RuntimeException($json['error']);
        }

        // Возвращение данных ответа сервера или полного ответа сервера, если данные отсутствуют.
        return $json['data'] ?? $response;
    }

    /**
     * Метод для отправки запроса на сервер с использованием токена безопасности.
     *
     * @param string $url URL-адрес сервера.
     * @param mixed $data Данные, которые нужно отправить на сервер.
     * @param string $agent Идентификатор пользователя.
     * @param string $ec_private Приватный ключ EC.
     * @return string|array Возвращает данные, полученные от сервера, или полный ответ сервера, если данные отсутствуют.
     * @throws Exception Если не удается подключиться к серверу.
     */
    final public static function requestV2(
        string $url,
        mixed  $data,
        string $agent,
        string $ec_private,
    ): string|array
    {
        // Создание токена безопасности с использованием данных и ключей безопасности.
        $token = LWT::encode(
            $data,
            $ec_private,
            'ES256K',
        );

        // Разделение токена на составляющие.
        [, $payload, $signature] = explode('.', $token);

        // Создание нового HTTP-клиента.
        $http = new Client();

        // Отправка запроса на сервер.
        $response = $http->request(
            $url,
            'POST',
            ['data' => $payload],
            ['X-API-SIGNATURE' => "LWT $signature"],
            false,
            [CURLOPT_USERAGENT => $agent]
        );

        // Проверка успешности запроса.
        if ($response === false) {
            throw new Exception('Не могу подключиться к серверу: ' . $http->getResponseClientError());
        }

        // Декодирование ответа сервера.
        $json = json_decode($response, true);

        // Проверка статуса ответа сервера.
        if (is_array($json) && isset($json['status']) && $json['status'] != 200 && isset($json['error'])) {
            throw new RuntimeException($json['error']);
        }

        // Возвращение данных ответа сервера или полного ответа сервера, если данные отсутствуют.
        return $json['data'] ?? $response;
    }

    /**
     * Метод для отправки запроса на сервер с использованием токена безопасности.
     *
     * @param string $url URL-адрес сервера.
     * @param mixed $data Данные, которые нужно отправить на сервер.
     * @param string $agent Идентификатор пользователя.
     * @param string $ec_private Приватный ключ EC.
     * @param string $rsa_public Публичный ключ RSA.
     * @return string|array Возвращает данные, полученные от сервера, или полный ответ сервера, если данные отсутствуют.
     * @throws RuntimeException Если статус ответа сервера не равен 200.
     * @throws Exception Если не удается подключиться к серверу.
     */
    final public static function requestV3(
        string $url,
        mixed  $data,
        string $agent,
        string $ec_private,
        string $rsa_public,
    ): string|array
    {
        // Создание токена безопасности с использованием данных и ключей безопасности.
        $token = LWT::encode(
            $data,
            $ec_private,
            'ES256K',
            $rsa_public,
        );

        // Разделение токена на составляющие.
        [, $payload, $signature] = explode('.', $token);

        // Создание нового HTTP-клиента.
        $http = new Client();

        // Отправка запроса на сервер.
        $response = $http->request(
            $url,
            'POST',
            ['data' => $payload],
            ['X-API-SIGNATURE' => "LWTv3 $signature"],
            false,
            [CURLOPT_USERAGENT => $agent]
        );

        // Проверка успешности запроса.
        if ($response === false) {
            throw new Exception('Не могу подключиться к серверу: ' . $http->getResponseClientError());
        }

        // Декодирование ответа сервера.
        $json = json_decode($response, true);

        // Проверка статуса ответа сервера.
        if (is_array($json) && isset($json['status']) && $json['status'] != 200 && isset($json['error'])) {
            throw new RuntimeException($json['error']);
        }

        // Возвращение данных ответа сервера или полного ответа сервера, если данные отсутствуют.
        return $json['data'] ?? $response;
    }
}