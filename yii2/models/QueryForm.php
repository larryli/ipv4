<?php
/**
 * QueryForm.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli_ipv4_yii2_app\models;

use Yii;
use yii\base\Model;

/**
 * Class QueryForm
 * @package larryli_ipv4_yii2_app\models
 */
class QueryForm extends Model
{
    /**
     * @var string ip
     */
    public $ip;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['ip', 'validateIP'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'ip' => 'IP v4 Address',
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateIP($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!filter_var($this->ip, FILTER_VALIDATE_IP)) {
                $this->addError($attribute, 'invalid ip address');
            }
        }
    }

    /**
     * query ip division
     * @return null|string[] null or division array
     */
    public function query()
    {
        if ($this->validate()) {
            $ip = ip2long($this->ip);
            $results = [];
            foreach ([
                         'full' => Full::className(),
                         'mini' => Mini::className(),
                         'china' => China::className(),
                         'world' => World::className(),
                     ] as $name => $index) {
                $results[$name] = $this->getResultFromModel($index, $ip);
            }
            return $results;
        }
        return null;
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }

    /**
     * @param Index $index
     * @param $ip
     * @return string
     */
    protected function getResultFromModel($index, $ip)
    {
        $result = '<span class="not-set">(not set)</span>';
        $model = $index::findOneByIp($ip);
        if (empty($model)) {
            return $result;
        }
        $division = $model->division;
        if (empty($division)) {
            return $result;
        }
        $result = $division->name;
        for ($parent = $division->parent; $parent != null; $parent = $parent->parent) {
            $result = $parent->name . "\t" . $result;
        }
        return $result;
    }
}
