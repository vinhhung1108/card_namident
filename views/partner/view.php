<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Partner */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Danh sách đối tác', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

// CSS kiểu "chip" gọn gàng
$this->registerCss(<<<CSS
.rel-box{margin-top:16px}
.rel-title{margin:0 0 8px;font-size:16px;font-weight:600}
.chips{display:flex;flex-wrap:wrap;gap:8px}
.chip{display:inline-block;padding:6px 10px;border-radius:999px;font-size:13px;line-height:1;white-space:nowrap;border:1px solid #e5e7eb;background:#f8fafc;color:#0f172a;text-decoration:none}
.chip:hover{background:#eef2ff;border-color:#c7d2fe}
.empty-hint{padding:10px 12px;border:1px dashed #e5e7eb;border-radius:10px;color:#64748b;background:#fafafa}
CSS);

?>
<div class="partner-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            // 'id',
            'name',
            'address',
            'phone',
            'email:email',
            'note:ntext',
        ],
    ]) ?>

  <div class="rel-box">
    <div class="rel-title">Dịch vụ khả dụng</div>

    <?php if (!empty($model->services)): ?>
      <div class="chips">
        <?php foreach ($model->services as $s): ?>
          <?= \yii\helpers\Html::a(
                \yii\helpers\Html::encode($s->name),
                ['service/view', 'id' => $s->id],
                ['class' => 'chip', 'target' => '_blank']
          ) ?>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-hint">Đối tác này chưa được cấu hình dịch vụ khả dụng.</div>
    <?php endif; ?>
  </div>

</div>
