<?php

use yii\BaseYii as Yii;
use yii\db\Schema;
use yii\db\Migration;

class m150909_153358_ipv4_index extends Migration
{
    public function up()
    {
        foreach (Yii::$app->ipv4->providers as $name => $provider) {
            if (!empty($provider)) {
                $table = $this->tableName($name);
                $this->createTable($table, [
                    'id' => Schema::TYPE_BIGPK,
                    'division_id' => Schema::TYPE_INTEGER,
                ]);
            }
        }
    }

    public function down()
    {
        foreach (Yii::$app->ipv4->providers as $name => $provider) {
            if (!empty($provider)) {
                $this->dropTable($this->tableName($name));
            }
        }
    }

    private function tableName($name)
    {
        return Yii::$app->ipv4->prefix . $name;
    }
}
