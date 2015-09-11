<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%ipv4_world}}".
 * @package app\models
 */
class World extends Index
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ipv4_world}}';
    }
}
