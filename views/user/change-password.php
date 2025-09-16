<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ChangePasswordForm */

$this->title = 'Đổi mật khẩu';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-change-password" style="max-width:520px">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card card-body">
        <?php $form = ActiveForm::begin(); ?>
            <?= $form->field($model, 'current_password')->passwordInput() ?>
            <?= $form->field($model, 'new_password')->passwordInput() ?>
            <?= $form->field($model, 'repeat_password')->passwordInput() ?>

            <div class="mt-2">
                <?= Html::submitButton('Đổi mật khẩu', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Quay lại', ['site/index'], ['class'=>'btn btn-outline-secondary']) ?>
            </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
