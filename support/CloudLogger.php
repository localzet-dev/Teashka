<?php

namespace support;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use support\protocols\LWP;

class CloudLogger extends AbstractProcessingHandler
{
    protected string $agent;

    public function __construct($agent, $level = Logger::DEBUG, bool $bubble = true)
    {
        $this->agent = $agent;
        parent::__construct($level, $bubble);
    }

    protected function write(array $record): void
    {
        try {
            LWP::request(
                getenv('CLOUD_SERVER') . 'log/' . $this->agent,
                [
                    'level' => $record['level_name'],
                    'message' => $record['message'],
                    'context' => $record['context'] ?? [],
                ],
                $this->agent,
                file_get_contents(base_path(getenv('CLOUD_SECURITY_ENCRYPTION'))),
                file_get_contents(base_path(getenv('CLOUD_SECURITY_SIGNATURE'))),
            );
        } catch (\Throwable) {

        }
    }
}