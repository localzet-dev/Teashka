<?php

namespace app\service;

use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;

class Dialogflow
{
    /**
     * Обрабатывает запрос к Dialogflow API и возвращает результат.
     *
     * @param string $sessionID Идентификатор сессии пользователя.
     * @param string $message Сообщение пользователя.
     * @return \Google\Cloud\Dialogflow\V2\QueryResult Результат запроса к Dialogflow.
     */
    public static function process($sessionID, $message)
    {
        // Создаем экземпляр клиента SessionsClient.
        $client = new SessionsClient(['credentials' => base_path() . '/resources/dialogflow.json']);

        // Формируем имя сессии.
        $session = self::getSessionName(config('app.dialogflow_project'), $sessionID);

        // Создаем экземпляр TextInput и задаем текст сообщения и язык.
        $textInput = self::createTextInput($message);

        // Создаем экземпляр QueryInput и задаем входные данные.
        $queryInput = self::createQueryInput($textInput);

        // Выполняем запрос к Dialogflow API для обнаружения намерения (intent).
        $response = self::detectIntent($client, $session, $queryInput);

        // Получаем результат запроса.
        $result = $response->getQueryResult();

        return $result;
    }

    /**
     * Формирует имя сессии.
     *
     * @param string $projectID Идентификатор проекта Dialogflow.
     * @param string $sessionID Идентификатор сессии пользователя.
     * @return string Имя сессии.
     */
    private static function getSessionName($projectID, $sessionID)
    {
        return "projects/{$projectID}/agent/sessions/{$sessionID}";
    }

    /**
     * Создает экземпляр TextInput и задает текст сообщения и язык.
     *
     * @param string $message Сообщение пользователя.
     * @return \Google\Cloud\Dialogflow\V2\TextInput Экземпляр TextInput.
     */
    private static function createTextInput($message)
    {
        $textInput = new TextInput();
        $textInput->setText($message);
        $textInput->setLanguageCode('ru-RU');

        return $textInput;
    }

    /**
     * Создает экземпляр QueryInput и задает входные данные.
     *
     * @param \Google\Cloud\Dialogflow\V2\TextInput $textInput Экземпляр TextInput.
     * @return \Google\Cloud\Dialogflow\V2\QueryInput Экземпляр QueryInput.
     */
    private static function createQueryInput($textInput)
    {
        $queryInput = new QueryInput();
        $queryInput->setText($textInput);

        return $queryInput;
    }

    /**
     * Выполняет запрос к Dialogflow API для обнаружения намерения (intent).
     *
     * @param \Google\Cloud\Dialogflow\V2\SessionsClient $client Экземпляр клиента SessionsClient.
     * @param string $session Имя сессии.
     * @param \Google\Cloud\Dialogflow\V2\QueryInput $queryInput Экземпляр QueryInput.
     * @return \Google\Cloud\Dialogflow\V2\DetectIntentResponse Результат запроса к Dialogflow.
     */
    private static function detectIntent($client, $session, $queryInput)
    {
        return $client->detectIntent($session, $queryInput);
    }
}
