<?php

use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Division */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Divisions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="division-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'title',
            'is_city:boolean',
            'parent_id',
            'parent.name',
            'parent.title',
            [
                'label' => 'View Parent',
                'visible' => !empty($model->parent_id),
                'format' => 'html',
                'value' => Html::a('View', ['view', 'id' => $model->parent_id]),
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

                ['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>

</div>
