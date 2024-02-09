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

namespace support\protocols;

use Exception;
use localzet\HTTP\Client;
use localzet\JWT;
use localzet\LWT;
use RuntimeException;
use support\Log;
use Throwable;
use Triangle\Engine\Exception\BusinessException;
use Triangle\Engine\Exception\InvalidAccessException;
use Triangle\Engine\Exception\InvalidAccessTokenException;

class LWP
{
    const V1 = 'JWT';
    const V2 = 'LWT';
    const V3 = 'LWTv3';

    /**
     * @param string $agent
     * @param string $payload
     * @param string $sign
     * @return array
     * @throws InvalidAccessException|Exception Проблема с клиентом
     * @throws InvalidAccessTokenException Проблема с токеном
     */
    public static function process(string $agent, string $payload, string $sign): array
    {
        $agent = strtolower($agent);
        [$type, $signature] = explode(' ', $sign);

        if (!$payload || !$signature) {
            throw new InvalidAccessTokenException("Некорректный токен авторизации");
        }

        $data = match ($type) {
            self::V1 => static::processV1($agent, $payload, $signature),
            self::V2 => static::processV2($agent, $payload, $signature),
            self::V3 => static::processV3($agent, $payload, $signature),

            default => throw new InvalidAccessTokenException("Некорректная цифровая подпись")
        };

        if (!$data || !is_array($data)) {
            throw new InvalidAccessException("Некорректные данные");
        }

        return $data;
    }

    /**
     * @throws RuntimeException Внутренняя проблема
     * @throws BusinessException Проблема с запросом
     */
    public static function request(
        string $url,
        mixed  $data,
        string $agent,
        string $ec_private,
        string $rsa_public = self::V2,
    )
    {
        $version = self::V3;
        try {
            if ($rsa_public === self::V1) {
                $version = self::V1;
                $token = JWT::encode(
                    $data,
                    $ec_private,
                    'HS256',
                );
            } elseif ($rsa_public === self::V2) {
                $version = self::V2;
                $token = LWT::encode(
                    $data,
                    $ec_private,
                    'ES256K',
                );
            } else {
                $token = LWT::encode(
                    $data,
                    $ec_private,
                    'ES256K',
                    $rsa_public,
                );
            }
        } catch (Throwable) {
            throw new RuntimeException("Ошибка протокола LWP");
        }

        [, $payload, $signature] = explode('.', $token);

        $http = new Client();
        $response = $http->request(
            $url,
            'POST',
            ['data' => $payload],
            ['X-API-SIGNATURE' => "$version $signature"],
            false,
            [CURLOPT_USERAGENT => $agent]
        );

        if (!str_contains($url, 'https://cloud.zorin.space/engine/v1/log/')) {
            Log::debug("LWP::$url", ['$response' => $response]);
        }

        if ($response === false) {
            throw new RuntimeException('Не могу подключиться к серверу: ' . $http->getResponseClientError());
        }

        // Декодирование ответа сервера.
        $json = json_decode($response, true);

        // Проверка статуса ответа сервера.
        if (is_array($json) && isset($json['status']) && $json['status'] != 200 && isset($json['error'])) {
            if ($json['status'] == 500) {
                throw new RuntimeException($json['error']);
            } else {
                throw new BusinessException($json['error']);
            }
        }

        // Возвращение данных ответа сервера или полного ответа сервера, если данные отсутствуют.
        return $json['data'] ?? $response;
    }

    /**
     * @param string $agent
     * @param string $payload
     * @param string $signature
     * @return array
     * @throws InvalidAccessException|Exception Проблема с клиентом
     * @throws InvalidAccessTokenException Проблема с токеном
     */
    protected static function processV1(string $agent, string $payload, string $signature): array
    {
        if (!file_exists(base_path("resources/security/$agent-jwt.pem"))) {
            throw new InvalidAccessException("Недопустимый клиент авторизации");
        }

        $header = str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode(
                json_encode(
                    [
                        'typ' => 'JWT',
                        'alg' => 'HS256',
                    ],
                    JSON_UNESCAPED_SLASHES
                )
            )
        );

        try {
            return (array)JWT::decode(
                "$header.$payload.$signature",
                file_get_contents(base_path("resources/security/$agent-jwt.pem")),
                'HS256',
            );
        } catch (Throwable) {
            throw new InvalidAccessTokenException("Некорректный токен авторизации");
        }
    }

    /**
     * @param string $agent
     * @param string $payload
     * @param string $signature
     * @return array
     * @throws InvalidAccessException|Exception Проблема с клиентом
     * @throws InvalidAccessTokenException Проблема с токеном
     */
    protected static function processV2(string $agent, string $payload, string $signature): array
    {
        if (!file_exists(base_path("resources/security/$agent-ec-public.pem"))) {
            throw new InvalidAccessException("Недопустимый клиент авторизации");
        }

        $header = str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode(
                json_encode(
                    [
                        'typ' => 'LWTv3',
                        'cty' => 'JWS',
                        'alg' => 'ES256K',
                    ],
                    JSON_UNESCAPED_SLASHES
                )
            )
        );

        try {
            return (array)LWT::decode(
                "$header.$payload.$signature",
                file_get_contents(base_path("resources/security/$agent-ec-public.pem")),
                'ES256K'
            );
        } catch (Throwable) {
            throw new InvalidAccessTokenException("Некорректный токен авторизации");
        }
    }

    /**
     * @param string $agent
     * @param string $payload
     * @param string $signature
     * @return array
     * @throws InvalidAccessException|Exception Проблема с клиентом
     * @throws InvalidAccessTokenException Проблема с токеном
     */
    protected static function processV3(string $agent, string $payload, string $signature): array
    {

        if (
            !file_exists(base_path("resources/security/$agent-ec-public.pem"))
            || !file_exists(base_path("resources/security/$agent-rsa-private.pem"))
        ) {
            throw new InvalidAccessException("Недопустимый клиент авторизации");
        }

        $header = str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode(
                json_encode(
                    [
                        'typ' => 'LWTv3',
                        'cty' => 'LZX',
                        'alg' => 'ES256K',
                        'enc' => 'AES-256-CBC+RSA'
                    ],
                    JSON_UNESCAPED_SLASHES
                )
            )
        );

        try {
            return (array)LWT::decode(
                "$header.$payload.$signature",
                file_get_contents(base_path("resources/security/$agent-ec-public.pem")),
                'ES256K',
                file_get_contents(base_path("resources/security/$agent-rsa-private.pem")),
            );
        } catch (Throwable) {
            throw new InvalidAccessTokenException("Некорректный токен авторизации");
        }
    }
}