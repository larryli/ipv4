<?php

use yii\BaseYii as Yii;
use yii\db\Schema;
use yii\db\Migration;

/**
 * Class m150909_153352_ipv4_division
 */
class m150909_153352_ipv4_division extends Migration
{
    /**
     *
     */
    public function up()
    {
        $table = $this->tableName();
        $this->createTable($table, [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . ' NOT NULL',
            'title' => Schema::TYPE_STRING . ' NOT NULL',
            'is_city' => Schema::TYPE_BOOLEAN,
            'parent_id' => Schema::TYPE_INTEGER,
        ]);
        $this->createIndex('is_city', $table, 'is_city');
        $this->createIndex('parent_id', $table, 'parent_id');
    }

    /**
     *
     */
    public function down()
    {
        $this->dropTable($this->tableName());
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    private function tableName()
    {
        /**
         * @var $ipv4 \larryli\ipv4\yii2\IPv4
         */
        $ipv4 = Yii::$app->get('ipv4');
        return $ipv4->prefix . 'divisions';
    }
}
