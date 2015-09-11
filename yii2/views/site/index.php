<?php
/**
 * index.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\QueryForm */
/* @var $results null|string[] */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
$this->title = 'IP v4 Query';
?>
<div class="site-index">

    <div class="query">
        <?php $form = ActiveForm::begin([
            'id' => 'query-form',
            'method' => 'get',
            'action' => ['site/index'],
        ]); ?>

        <?= $form->field($model, 'ip') ?>

        <div class="form-group">
            <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <?php if (!empty($results)): ?>
    <div class="body-content">
        <div class="row">
            <?php foreach ($results as $name => $result): ?>
            <div class="col-lg-6">
                <h2><?= $name; ?></h2>
                <p><?= $result; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
