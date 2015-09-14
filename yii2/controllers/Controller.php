<?php
/**
 * Controller.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli_ipv4_yii2_app\controllers;

use yii\web\Controller as BaseController;
use larryli_ipv4_yii2_app\models\China;
use larryli_ipv4_yii2_app\models\Full;
use larryli_ipv4_yii2_app\models\Mini;
use larryli_ipv4_yii2_app\models\World;
use yii\web\NotFoundHttpException;

/**
 * Class Controller
 * @package larryli_ipv4_yii2_app\controllers
 */
class Controller extends BaseController
{
    /**
     * @param $type
     * @return Full|Mini|China|World
     * @throws NotFoundHttpException
     */
    protected function getIndexClass($type)
    {
        switch ($type) {
            case 'full':
                return Full::className();
            case 'mini':
                return Mini::className();
            case 'china':
                return China::className();
            case 'world':
                return World::className();
            default:
                throw new NotFoundHttpException("Type {$type} not found");
        }
    }
}