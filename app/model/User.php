<?php

namespace app\model;

use Triangle\MongoDB\Model;

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
    public const VERIFY = 2;

    /**
     * Статус пользователя: аккаунт активирован.
     */
    public const DONE = 3;

    public static function isRegistered(string $login): bool
    {
        return static::where(['login' => $login])->exists();
    }

    public function state(int $state): bool
    {
        return $this->update(['state' => $state]);
    }

    public function delAttempt()
    {
        Attempts::where('user', $this->id)->delete();
    }

    public function addAttempt(string $login)
    {
        Attempts::updateOrCreate(['user' => $this->id], ['login' => $login]);
    }
}
