<?php

use yii\BaseYii as Yii;
use yii\db\Schema;
use yii\db\Migration;

class m150909_153352_ipv4_division extends Migration
{
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

    public function down()
    {
        $this->dropTable($this->tableName());
    }

    private function tableName()
    {
        return Yii::$app->ipv4->prefix . 'divisions';
    }
}
