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

namespace app\model;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Модель пользователя.
 *
 * @property int $id Идентификатор пользователя.
 * @property int $state Статус пользователя.
 * @property string $login Логин в ЭИОС ДГТУ.
 * @property string $token Токен пользователя в UniT Global.
 */
class User extends Model
{
    /**
     * Имя подключения к MongoDB.
     *
     * @var string
     */
    protected $connection = 'Teashka';

    /**
     * Имя коллекции в MongoDB.
     *
     * @var string
     */
    protected $collection = 'Users';

    /**
     * Имя первичного ключа.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Тип данных первичного ключа.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Атрибуты, которые не могут быть массово заполнены.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Значения атрибутов по умолчанию.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Указывает наличие временных меток "created_at" и "updated_at".
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Статус пользователя: начальное состояние.
     */
    public const START = 1;

    /**
     * Статус пользователя: ожидание подтверждения.
     */
    public const PENDING = 2;

    /**
     * Статус пользователя: аккаунт активирован.
     */
    public const DONE = 3;

    public static function isRegistered(string $login): bool
    {
        return static::where(['login' => $login])->exists();
    }

    public function addAttempt(string $login, int $user_id): void
    {
        Attempts::updateOrCreate(['user' => $this->id], ['login' => $login, 'user_id' => $user_id]);
    }

    public function getAttempt(): Attempts
    {
        /** @var Attempts */
        return Attempts::where('user', $this->id)->first();
    }

    public function delAttempt(): void
    {
        Attempts::where('user', $this->id)->delete();
    }

    public static function find($id)
    {
        return static::where(['id' => $id])->first();
    }
}
