<?php

namespace app\model;

use support\MongoModel;

class Attempts extends MongoModel
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
}
