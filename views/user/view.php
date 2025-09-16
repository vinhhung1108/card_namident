<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Người dùng', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$statusMap = [10 => 'Active', 0 => 'Inactive'];
?>
<div class="user-view">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="m-0"><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('Cập nhật', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?php if ((int)$model->id !== 1): // tuỳ chính sách, chặn xoá id=1 ?>
                <?= Html::a('Xoá', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Bạn chắc chắn muốn xoá người dùng này?',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <?= DetailView::widget([
        'model' => $model,
        'template' => '<tr><th style="width:220px">{label}</th><td>{value}</td></tr>',
        'attributes' => [
            'id',
            'username',
            'email:email',
            ['label'=>'Họ tên','value'=>$model->full_name],
            [
                'attribute'=>'status',
                'value' => $statusMap[$model->status] ?? $model->status,
            ],
            ['attribute'=>'created_at','label'=>'Tạo lúc','format'=>['datetime','php:d/m/Y H:i']],
            ['attribute'=>'updated_at','label'=>'Cập nhật','format'=>['datetime','php:d/m/Y H:i']],
        ],
    ]) ?>

</div>
