<?php
/**
 * SiteController.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

namespace larryli_ipv4_yii2_app\controllers;

use larryli_ipv4_yii2_app\models\QueryForm;
use Yii;
use yii\web\Controller;

class SiteController extends Controller
{
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new QueryForm();
        $model->load(Yii::$app->request->get());
        if (empty($model->ip)) {
            $model->ip = Yii::$app->request->userIP;
        }
        $results = $model->query();
        return $this->render('index', [
            'model' => $model,
            'results' => $results,
        ]);
    }
}
