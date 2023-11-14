<?php

namespace app\actions;

use app\service\UniT;
use app\service\Telegram;

class Schedule
{
  /**
   * Обрабатывает расписание.
   *
   * @param int|null $chatId Идентификатор чата (по умолчанию null).
   * @param array|null $parameters Параметры расписания (по умолчанию ['date-time' => '']).
   * @return void
   */
  public static function process(?int $chatId = null, ?array $parameters = ['date-time' => '']): void
  {
    $start = self::getStartDateTime($parameters);
    $end = self::getEndDateTime($parameters, $start);

    $schedule = UniT::getSchedule($start, $end);

    if (empty($schedule)) {
      Telegram::sendMessage("Занятий нет", $chatId);
      return;
    }

    self::sendScheduleAsText($schedule, $chatId);
  }

  /**
   * Отправляет расписание в виде текста.
   *
   * @param array $schedule Расписание.
   * @param int|null $chatId Идентификатор чата (по умолчанию null).
   * @return void
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
      $type = $item['type'] ?? '';

      $message = <<<MSG_EOF
      ⏰<b>$start-$end</b> ($date)

      📚<b>{$item['module']}</b>
      {$type}
      {$item['theme']}
      🚪<b>Аудитория:</b> $location
      <b>$teachersLabel:</b> {$item['teacher']}
      <b>$groupsLabel:</b> {$item['group']}
      MSG_EOF;

      Telegram::sendMessage($message, $chatId);
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
