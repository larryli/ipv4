<?php
/**
 * Index.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace app\models;

use larryli\ipv4\yii2\models\Index as BaseIndex;

/**
 * Class Index
 * @package app\models
 *
 * @property string $startIp
 * @property string $ip
 * @property Division $division
 */
abstract class Index extends BaseIndex
{
    /**
     * @return string
     */
    static public function divisionClassName()
    {
        return Division::className();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'startIp' => 'Start IP',
        ]);
    }

    /**
     * @return string
     */
    public function getStartIp()
    {
        $model = static::find()
            ->where(['<', 'id', $this->id])
            ->orderBy('id DESC')
            ->one();
        if (!empty($model)) {
            return long2ip($model->id + 1);
        }
        return '0.0.0.0';
    }
}