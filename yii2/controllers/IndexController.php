<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * IndexController implements the CRUD actions for Ipv4Full model.
 */
class IndexController extends Controller
{
    /**
     * Lists all Ipv4Full models.
     * @param string $type
     * @return mixed
     */
    public function actionIndex($type)
    {
        $class = $this->getIndexClass($type);
        $dataProvider = new ActiveDataProvider([
            'query' => $class::find(),
        ]);

        return $this->render('index', [
            'type' => $type,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Ipv4Full model.
     * @param integer $id
     * @param string $type
     * @return mixed
     */
    public function actionView($id, $type)
    {
        return $this->render('view', [
            'type' => $type,
            'model' => $this->findModel($id, $type),
        ]);
    }

    /**
     * Finds the Ipv4Full model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param string $type
     * @return \app\models\Full|\app\models\Mini|\app\models\China|\app\models\World the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, $type)
    {
        $class = $this->getIndexClass($type);
        if (($model = $class::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
