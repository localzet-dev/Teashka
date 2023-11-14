<?php

namespace app\service;

use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use Google\Cloud\Dialogflow\V2\DetectIntentResponse;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\QueryResult;
use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;

class Dialogflow
{
    /**
     * Обрабатывает запрос к Dialogflow API, и возвращает результат.
     *
     * @param string $sessionID Идентификатор сессии пользователя.
     * @param string $message Сообщение пользователя.
     * @return QueryResult Результат запроса к Dialogflow.
     * @throws ValidationException
     * @throws ApiException
     */
    public static function process(string $sessionID, string $message): QueryResult
    {
        // Создаем экземпляр клиента SessionsClient.
        $client = new SessionsClient(['credentials' => getenv('DF_CREDENTIALS')]);

        // Формируем имя сессии.
        $session = self::getSessionName(getenv('DF_PROJECT_ID'), $sessionID);

        // Создаем экземпляр TextInput и задаем текст сообщения и язык.
        $textInput = self::createTextInput($message);

        // Создаем экземпляр QueryInput и задаем входные данные.
        $queryInput = self::createQueryInput($textInput);

        // Выполняем запрос к Dialogflow API для обнаружения намерения (intent).
        $response = self::detectIntent($client, $session, $queryInput);

        // Получаем результат запроса.
        return $response->getQueryResult();
    }

    /**
     * Формирует имя сессии.
     *
     * @param string $projectID Идентификатор проекта Dialogflow.
     * @param string $sessionID Идентификатор сессии пользователя.
     * @return string Имя сессии.
     */
    private static function getSessionName(string $projectID, string $sessionID): string
    {
        return "projects/{$projectID}/agent/sessions/{$sessionID}";
    }

    /**
     * Создает экземпляр TextInput, и задает текст сообщения и язык.
     *
     * @param string $message Сообщение пользователя.
     * @return TextInput Экземпляр TextInput.
     */
    private static function createTextInput(string $message): TextInput
    {
        $textInput = new TextInput();
        $textInput->setText($message);
        $textInput->setLanguageCode('ru-RU');

        return $textInput;
    }

    /**
     * Создает экземпляр QueryInput и задает входные данные.
     *
     * @param TextInput $textInput Экземпляр TextInput.
     * @return QueryInput Экземпляр QueryInput.
     */
    private static function createQueryInput(TextInput $textInput): QueryInput
    {
        $queryInput = new QueryInput();
        $queryInput->setText($textInput);

        return $queryInput;
    }

    /**
     * Выполняет запрос к Dialogflow API для обнаружения намерения (intent).
     *
     * @param SessionsClient $client Экземпляр клиента SessionsClient.
     * @param string $session Имя сессии.
     * @param QueryInput $queryInput Экземпляр QueryInput.
     * @return DetectIntentResponse Результат запроса к Dialogflow.
     * @throws ApiException
     */
    private static function detectIntent(SessionsClient $client, string $session, QueryInput $queryInput): DetectIntentResponse
    {
        return $client->detectIntent($session, $queryInput);
    }
}
