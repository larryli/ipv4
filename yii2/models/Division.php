<?php
/**
 * Division.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace app\models;

use larryli\ipv4\yii2\models\Division as BaseDivision;

/**
 * Class Division
 * @package app\models
 *
 * @property Full $full
 * @property Mini $mini
 * @property China $china
 * @property World $world
 */
class Division extends BaseDivision
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return "{{%ipv4_divisions}}";
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFull()
    {
        return $this->getIndex(Full::className());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMini()
    {
        return $this->getIndex(Mini::className());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChina()
    {
        return $this->getIndex(China::className());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWorld()
    {
        return $this->getIndex(World::className());
    }
}