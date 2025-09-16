<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Referral */

$this->title = 'Tạo mới mã giới thiệu';
$this->params['breadcrumbs'][] = ['label' => 'Danh sách mã giới thiệu', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="referral-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
