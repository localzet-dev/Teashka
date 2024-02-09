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

namespace app\actions;

use app\repositories\UniT;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Triangle\Engine\Exception\BusinessException;

class Schedule
{
    /**
     * @param array $arguments
     * @throws BusinessException
     */
    public static function handleCommand(array $arguments): void
    {
        static::process(request()->chat->id);
    }

    /**
     * @param array $parameters
     * @throws BusinessException
     */
    public static function handleIntent(array $parameters): void
    {
        static::process(request()->chat->id, $parameters);
    }

    /**
     * Обрабатывает расписание.
     *
     * @param int|null $chatId Идентификатор чата (по умолчанию null).
     * @param array|null $parameters Параметры расписания (по умолчанию ['date-time' => '']).
     * @return void
     * @throws BusinessException
     * @throws \Exception
     */
    public static function process(?int $chatId = null, ?array $parameters = ['date-time' => '']): void
    {
        $start = self::getStartDateTime($parameters);
        $end = self::getEndDateTime($parameters, $start);

        $schedule = UniT::getSchedule(request()->user->user_id, $start, $end);

        if (empty($schedule)) {
            throw new BusinessException("Занятий нет");
        }

        self::sendScheduleAsText($schedule, $chatId);
    }

    /**
     * Отправляет расписание в виде текста.
     *
     * @param array $schedule Расписание.
     * @param int|null $chatId Идентификатор чата (по умолчанию null).
     * @return void
     * @throws TelegramSDKException
     */
    private static function sendScheduleAsText(array $schedule, ?int $chatId): void
    {
        foreach ($schedule as $item) {
            $date = date('d.m.Y', strtotime($item['start']));
            $start = date('H:i', strtotime($item['start']));
            $end = date('H:i', strtotime($item['end']));

            if (!$item['teacher']) continue;

            $teachersLabel = str_contains($item['teacher'], ',') ? 'Преподаватели' : 'Преподаватель';
            $groupsLabel = str_contains($item['group'], ',') ? 'Группы' : 'Группа';
            $location = empty($item['link']) ? $item['location'] : "<a href=\"{$item['link']}\">{$item['location']}</a>";

            $title = $item['title'] ?? $item['module'];
            $type = ($item['type'] && $item['type'] != ' ') ? "\n" . $item['type'] : '';
            $theme = ($item['theme'] && $item['theme'] != ' ') ? "\n" . $item['theme'] : '';

            $message = <<<MSG_EOF
            ⏰<b>$start-$end</b> ($date)
            
            📚<b>{$title}</b>{$type}{$theme}
            🚪<b>Аудитория:</b> $location
            
            <b>$teachersLabel:</b> {$item['teacher']}
            <b>$groupsLabel:</b> {$item['group']}
            MSG_EOF;

            request()->telegram->sendMessage($message, request()->chat->id);
        }
    }

    /**
     * Получает дату и время начала.
     *
     * @param array|null $parameters Параметры расписания (по умолчанию null).
     * @return string Дата и время начала в формате 'd.m.Y'.
     */
    private static function getStartDateTime(?array $parameters): string
    {
        if ($parameters && !empty($parameters['date-time'])) {
            $dateTime = $parameters['date-time'];
            if (is_array($dateTime)) {
                return date('d.m.Y', strtotime($dateTime['date_time'] ?? $dateTime['startDate'] ?? date('d.m.Y')));
            } elseif (is_string($dateTime)) {
                return date('d.m.Y', strtotime($dateTime));
            }
        }

        if ((int)date('H') >= 19) {
            return date('d.m.Y', strtotime(date('d.m.Y') . ' +1 day'));
        }

        return date('d.m.Y');
    }

    /**
     * Получает дату и время конца.
     *
     * @param array|null $parameters Параметры расписания (по умолчанию null).
     * @param string $start Дата и время начала в формате 'd.m.Y'.
     * @return string Дата и время конца в формате 'd.m.Y'.
     */
    private static function getEndDateTime(?array $parameters, string $start): string
    {
        if ($parameters && !empty($parameters['date-time']) && is_array($parameters['date-time'])) {
            $dateTime = $parameters['date-time'];
            return date('d.m.Y', strtotime($dateTime['endDate'] ?? date('d.m.Y', strtotime($start . ' +1 day'))));
        }

        return date('d.m.Y', strtotime($start . ' +1 day'));
    }
}
