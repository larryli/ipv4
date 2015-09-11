<?php
/**
 * main.php
 *
 * Author: Larry Li <larryli@qq.com>
 */

/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
AppAsset::register($this);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
    <?php $this->beginBody() ?>

    <div class="wrap">
        <?php
        NavBar::begin([
            'brandLabel' => 'IP v4 Query',
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar-inverse navbar-fixed-top',
            ],
        ]);
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-right'],
            'items' => [
                ['label' => 'Home', 'url' => ['/site/index']],
                ['label' => 'Divisions', 'url' => ['/division/index']],
                [
                    'label' => 'Index',
                    'items' => [
                        ['label' => 'Full', 'url' => ['/index/index', 'type' => 'full']],
                        ['label' => 'Mini', 'url' => ['/index/index', 'type' => 'mini']],
                        ['label' => 'China', 'url' => ['/index/index', 'type' => 'china']],
                        ['label' => 'World', 'url' => ['/index/index', 'type' => 'world']],
                    ],
                ],
                ['label' => 'Fork from Github', 'url' => 'https://github.com/larryli/ipv4'],
            ],
        ]);
        NavBar::end();
        ?>

        <div class="container">
            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
            <?= $content ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p class="pull-left">&copy; <?= Html::a('larryli/ipv4', 'https://packagist.org/packages/larryli/ipv4'); ?> <?= date('Y') ?></p>

            <p class="pull-right"><?= Yii::powered() ?></p>
        </div>
    </footer>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>

