<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Division */
/* @var $type string */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = ucfirst($type) . ' Index: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Divisions', 'url' => ['index']];
$this->render('_parent', ['model' => $model]);
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = ucfirst($type) . ' Index';
?>
<div class="ipv4-full-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'ip',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
                'urlCreator' => function ($action, $model, $key, $index) use ($type) {
                    $params = is_array($key) ? $key : ['id' => (string) $key];
                    $params[0] = 'index/' . $action;
                    $params['type'] = $type;
                    return Url::toRoute($params);
                },
            ],
        ],
    ]); ?>

</div>
