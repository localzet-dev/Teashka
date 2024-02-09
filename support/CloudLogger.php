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