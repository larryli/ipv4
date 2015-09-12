<?php

namespace larryli_ipv4_yii2_app\models;

use Yii;

/**
 * This is the model class for table "{{%ipv4_world}}".
 * @package larryli_ipv4_yii2_app\models
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
