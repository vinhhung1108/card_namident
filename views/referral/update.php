<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Referral */

$this->title = 'Cập nhật thông tin: ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Danh sách mã giới thiệu', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Cập nhật';
?>
<div class="referral-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
