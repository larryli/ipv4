<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $type string */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = ucfirst($type) . ' Index';
$this->params['breadcrumbs'][] = 'Index';
$this->params['breadcrumbs'][] = ucfirst($type);
?>
<div class="ipv4-full-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'ip',
            'division_id',
            'division.name',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {division}',
                'buttons' => [
                    'division' => function ($url, $model, $key) {
                        $options = [
                            'title' => 'View Division',
                            'aria-label' => 'View Division',
                            'data-pjax' => '0',
                        ];
                        return empty($model->division_id) ? '' : Html::a('<span class="glyphicon glyphicon-globe"></span>', ['division/view', 'id' => $model->division_id], $options);
                    },
                ],
                'urlCreator' => function ($action, $model, $key, $index) use ($type) {
                    $params = is_array($key) ? $key : ['id' => (string) $key];
                    $params[0] = $action;
                    $params['type'] = $type;
                    return Url::toRoute($params);
                },
            ],
        ],
    ]); ?>

</div>
