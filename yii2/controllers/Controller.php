<?php
/**
 * Controller.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace app\controllers;

use yii\web\Controller as BaseController;
use app\models\China;
use app\models\Full;
use app\models\Mini;
use app\models\World;
use yii\web\NotFoundHttpException;

/**
 * Class Controller
 * @package app\controllers
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