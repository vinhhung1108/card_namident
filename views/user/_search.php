<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\UserSearch */
/* @var $form yii\widgets\ActiveForm */

$statusMap = [10 => 'Active', 0 => 'Inactive'];
?>
<div class="user-search card card-body">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-md-2"><?= $form->field($model, 'id') ?></div>
        <div class="col-md-3"><?= $form->field($model, 'username') ?></div>
        <div class="col-md-3"><?= $form->field($model, 'email') ?></div>
        <div class="col-md-4"><?= $form->field($model, 'full_name')->label('Họ tên') ?></div>
    </div>

    <div class="row">
        <div class="col-md-2"><?= $form->field($model, 'status')->dropDownList($statusMap, ['prompt'=>'-- Tất cả --']) ?></div>
        <div class="col-md-5">
            <label class="form-label d-block">Ngày tạo (từ → đến)</label>
            <div class="d-flex gap-2">
                <?= Html::activeTextInput($model, 'created_from', ['class'=>'form-control','placeholder'=>'dd/mm/yyyy']) ?>
                <?= Html::activeTextInput($model, 'created_to',   ['class'=>'form-control','placeholder'=>'dd/mm/yyyy']) ?>
            </div>
        </div>
        <div class="col-md-5">
            <label class="form-label d-block">Ngày cập nhật (từ → đến)</label>
            <div class="d-flex gap-2">
                <?= Html::activeTextInput($model, 'updated_from', ['class'=>'form-control','placeholder'=>'dd/mm/yyyy']) ?>
                <?= Html::activeTextInput($model, 'updated_to',   ['class'=>'form-control','placeholder'=>'dd/mm/yyyy']) ?>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <?= Html::submitButton('Tìm kiếm', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Xoá lọc', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
