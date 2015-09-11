<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $type string */
/* @var $model app\models\Full|app\models\Mini|app\models\China|app\models\World */

$this->title = $model->ip;
$this->params['breadcrumbs'][] = 'Index';
$this->params['breadcrumbs'][] = ['label' => ucfirst($type), 'url' => ['index', 'type' => $type]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ipv4-full-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= empty($model->division_id) ? '' : Html::a('View Division', ['division/view', 'id' => $model->division_id], ['class' => 'btn btn-success']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'startIp',
            'ip',
            'division_id',
            'division.name',
            'division.title',
            'division.is_city:boolean',
        ],
    ]) ?>

</div>
