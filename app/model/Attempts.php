<?php

namespace app\model;

use Triangle\MongoDB\Model;

class Attempts extends Model
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
    protected $collection = 'Attempts';

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

    public static function byUser(int $user_id): array
    {
        return static::where('user', $user_id)?->get()?->toArray() ?? [];
    }

    public static function byLogin(string $login): array
    {
        return static::where('login', $login)?->get()?->toArray() ?? [];
    }
}
