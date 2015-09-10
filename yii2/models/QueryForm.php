<?php
/**
 * QueryForm.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace app\models;

use Yii;
use yii\base\Model;

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
            /**
             * @var $ipv4 \larryli\ipv4\yii2\IPv4
             */
            $ipv4 = Yii::$app->get('ipv4');
            foreach ($ipv4->getQueries() as $name => $query) {
                $results[$name] = $query->find($ip);
            }
            return $results;
        }
        return null;
    }

    public function formName()
    {
        return '';
    }
}
