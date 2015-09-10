<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Division */

$this->title = 'Create Division';
$this->params['breadcrumbs'][] = ['label' => 'Divisions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="division-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
