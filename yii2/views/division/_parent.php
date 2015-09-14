<?php
/**
 * _parent.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

/* @var $this yii\web\View */
/* @var $model larryli_ipv4_yii2_app\models\Division */

$breadcrumbs = [];
for ($parent = $model->parent; $parent != null; $parent = $parent->parent) {
    $breadcrumbs[] = ['label' => $parent->name, 'url' => ['view', 'id' => $parent->id]];
}
$this->params['breadcrumbs'] = array_merge($this->params['breadcrumbs'], array_reverse($breadcrumbs));
