<?php

namespace larryli\ipv4\yii2\models;

use Yii;

/**
 * This is the model class for table "{{%ipv4_divisions}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $title
 * @property boolean $is_city
 * @property integer $parent_id
 */
class Division extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        /**
         * @var $ipv4 \larryli\ipv4\yii2\IPv4
         */
        $ipv4 = Yii::$app->get('ipv4');
        return "{{%{$ipv4->prefix}divisions}}";
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'title'], 'required'],
            [['is_city'], 'boolean'],
            [['parent_id'], 'integer'],
            [['name', 'title'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'title' => 'Title',
            'is_city' => 'Is City',
            'parent_id' => 'Parent ID',
            'parent.name' => 'Parent Name',
            'parent.title' => 'Parent Title',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(self::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(self::className(), ['parent_id' => 'id']);
    }
}
