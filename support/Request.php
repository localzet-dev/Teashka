<?php

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/localzet/FrameX      FrameX Project v1-2
 * @link        https://github.com/Triangle-org/Engine  Triangle Engine v2+
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 UniT Group
 * @license     https://www.gnu.org/licenses/agpl AGPL-3.0 license
 *
 *              This program is free software: you can redistribute it and/or modify
 *              it under the terms of the GNU Affero General Public License as
 *              published by the Free Software Foundation, either version 3 of the
 *              License, or (at your option) any later version.
 *
 *              This program is distributed in the hope that it will be useful,
 *              but WITHOUT ANY WARRANTY; without even the implied warranty of
 *              MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *              GNU Affero General Public License for more details.
 *
 *              You should have received a copy of the GNU Affero General Public License
 *              along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace support;

use AllowDynamicProperties;
use app\model\User;
use app\service\Telegram;
use Telegram\Bot\Objects\Chat;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;

/**
 * Class Request
 */
#[AllowDynamicProperties]
class Request extends \Triangle\Engine\Http\Request
{
    public ?Telegram $telegram;
    public ?Update $input;

    public ?string $type;
    public ?Chat $chat;
    public ?Message $message;

    public ?User $user;
}
