<?php

namespace app\service;

use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\QueryResult;
use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;

class Dialogflow
{
    private SessionsClient $client;
    private string $session;

    /**
     * Dialogflow constructor.
     * @param string $sessionID
     * @throws ValidationException
     */
    public function __construct(string $sessionID)
    {
        $this->client = new SessionsClient(['credentials' => base_path('dialogflow.json')]);
        $this->session = $this->client->sessionName(getenv('DF_PROJECT_ID'), $sessionID ?: uniqid());
    }

    /**
     * Создает экземпляр TextInput.
     *
     * @param string $text
     * @return TextInput
     */
    private function createTextInput(string $text): TextInput
    {
        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode('ru-RU');

        return $textInput;
    }

    /**
     * Создает экземпляр QueryInput.
     *
     * @param TextInput $textInput
     * @return QueryInput
     */
    private function createQueryInput(TextInput $textInput): QueryInput
    {
        $queryInput = new QueryInput();
        $queryInput->setText($textInput);

        return $queryInput;
    }

    /**
     * Обрабатывает запрос к Dialogflow API, и возвращает результат.
     *
     * @param string $text
     * @return QueryResult
     * @throws ApiException
     */
    public function detectIntent(string $text): QueryResult
    {
        $textInput = $this->createTextInput($text);
        $queryInput = $this->createQueryInput($textInput);

        $response = $this->client->detectIntent($this->session, $queryInput);
        $this->client->close();

        return $response->getQueryResult();
    }
}
