<?php

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
