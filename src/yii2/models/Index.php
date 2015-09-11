<?php

namespace larryli\ipv4\yii2\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%{$prefix}{$index}}}".
 *
 * you must implement method tableName()
 *
 * ```php
 * class YourFull extends \larryli\ipv4\yii2\models\Index
 * {
 *     public static function tableName()
 *     {
 *         return "{{%your_full}}";
 *     }
 * }
 * ```
 *
 * @property integer $id
 * @property integer $division_id
 * @property string $ip
 * @property Division $division
 */
abstract class Index extends ActiveRecord
{
    /**
     * @param integer $ip
     * @return null|Index
     */
    static public function findOneByIp($ip)
    {
        return static::find()
            ->where(['>=', 'id', $ip])
            ->orderBy('id ASC')
            ->one();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['division_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip' => 'End IP',
            'division_id' => 'Division ID',
            'division.name' => 'Division Name',
            'division.title' => 'Division Title',
            'division.is_city' => 'Division Is City',
        ];
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return long2ip($this->id);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivision()
    {
        return $this->hasOne(static::divisionClassName(), ['id' => 'division_id']);
    }

    /**
     * @return string
     */
    static public function divisionClassName()
    {
        return Division::className();
    }
}
