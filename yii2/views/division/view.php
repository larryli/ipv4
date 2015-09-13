<?php

use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model larryli_ipv4_yii2_app\models\Division */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Divisions', 'url' => ['index']];
$this->render('_parent', ['model' => $model]);
$this->params['breadcrumbs'][] = $model->name;

?>
<div class="division-view">

    <h1><?= Html::encode($model->title) ?></h1>

    <p>
        <?= Html::a('View Full Index', ['indexes', 'id' => $model->id, 'type' => 'full'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('View Mini Index', ['indexes', 'id' => $model->id, 'type' => 'mini'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('View China Index', ['indexes', 'id' => $model->id, 'type' => 'china'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('View World Index', ['indexes', 'id' => $model->id, 'type' => 'world'], ['class' => 'btn btn-success']) ?>
        <?= empty($model->parent) ? '' : Html::a('View Parent', ['view', 'id' => $model->parent_id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'title',
            'is_city:boolean',
            'parent_id',
            [
                'label' => 'Parent Name',
                'visible' => !empty($model->parent),
                'value' => empty($model->parent) ? '' : $model->parent->name,
            ],
            [
                'label' => 'Parent Title',
                'visible' => !empty($model->parent),
                'value' => empty($model->parent) ? '' : $model->parent->title,
            ],
        ],
    ]) ?>

        <?php $dataProvider = new ActiveDataProvider([
            'query' => $model->getChildren(),
            'pagination' => false,
            ]);
        ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                'id',
                'name',
                'title',
                'is_city:boolean',

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                ],
            ],
        ]); ?>

</div>
