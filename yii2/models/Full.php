<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%ipv4_full}}".
 * @package app\models
 */
class Full extends Index
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ipv4_full}}';
    }
}
