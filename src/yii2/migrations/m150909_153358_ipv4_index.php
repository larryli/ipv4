<?php

use yii\BaseYii as Yii;
use yii\db\Schema;
use yii\db\Migration;

/**
 * Class m150909_153358_ipv4_index
 */
class m150909_153358_ipv4_index extends Migration
{
    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function up()
    {
        /**
         * @var $ipv4 \larryli\ipv4\yii2\IPv4
         */
        $ipv4 = Yii::$app->get('ipv4');
        foreach ($ipv4->providers as $name => $provider) {
            if (!empty($provider)) {
                $table = $this->tableName($name);
                $this->createTable($table, [
                    'id' => Schema::TYPE_BIGPK,
                    'division_id' => Schema::TYPE_INTEGER,
                ]);
            }
        }
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function down()
    {
        /**
         * @var $ipv4 \larryli\ipv4\yii2\IPv4
         */
        $ipv4 = Yii::$app->get('ipv4');
        foreach ($ipv4->providers as $name => $provider) {
            if (!empty($provider)) {
                $this->dropTable($this->tableName($name));
            }
        }
    }

    /**
     * @param $name
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    private function tableName($name)
    {
        /**
         * @var $ipv4 \larryli\ipv4\yii2\IPv4
         */
        $ipv4 = Yii::$app->get('ipv4');
        return $ipv4->prefix . $name;
    }
}
