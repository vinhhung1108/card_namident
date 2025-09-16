<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */

$statusMap = [10 => 'Active', 0 => 'Inactive'];
?>
<div class="user-form card card-body">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-4"><?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'email')->input('email')->textInput(['maxlength' => true]) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'full_name')->textInput(['maxlength' => true])->label('Họ tên') ?></div>
    </div>

    <div class="row">
        <div class="col-md-4"><?= $form->field($model, 'status')->dropDownList($statusMap) ?></div>
    </div>

    <hr>

    <?php if ($model->isNewRecord): ?>
        <div class="row">
            <div class="col-md-4"><?= $form->field($model, 'password')->passwordInput() ?></div>
            <div class="col-md-4"><?= $form->field($model, 'password_repeat')->passwordInput() ?></div>
        </div>
    <?php else: ?>
        <div class="alert alert-secondary">
            Đổi mật khẩu: để trống nếu không thay đổi.
        </div>
        <div class="row">
            <div class="col-md-4"><?= $form->field($model, 'password')->passwordInput(['value'=>'']) ?></div>
            <div class="col-md-4"><?= $form->field($model, 'password_repeat')->passwordInput(['value'=>'']) ?></div>
        </div>
    <?php endif; ?>

    <div class="mt-3">
        <?= Html::submitButton($model->isNewRecord ? 'Tạo' : 'Lưu', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Quay lại', ['index'], ['class'=>'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
