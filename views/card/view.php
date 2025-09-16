<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

use yii\helpers\Url;
$qrImg  = Url::to(['card/qr','code'=>$model->card_code], true);
$qrDl   = Url::to(['card/qr','code'=>$model->card_code,'download'=>1], true);
$pdfDl  = Url::to(['card/qr-pdf','code'=>$model->card_code], true);
$pubUrl = Url::to(['card/show','code'=>$model->card_code], true);

/* CSS nhẹ nhàng cho chip */
$this->registerCss(<<<CSS
.rel-box{margin-top:10px}
.rel-title{margin:0 0 8px;font-size:16px;font-weight:600}
.chips{display:flex;flex-wrap:wrap;gap:8px}
.chip{display:inline-block;padding:6px 10px;border-radius:999px;font-size:13px;line-height:1;white-space:nowrap;border:1px solid #e5e7eb;background:#f8fafc;color:#0f172a;text-decoration:none}
.chip:hover{background:#eef2ff;border-color:#c7d2fe}
.chip-info{background:#ecfeff;border-color:#a5f3fc;color:#075985}
.chip-info:hover{background:#cffafe;border-color:#67e8f9}
.chip-secondary{background:#f1f5f9;border-color:#cbd5e1;color:#0f172a}
.chip-secondary:hover{background:#e2e8f0}
.empty-hint{padding:10px 12px;border:1px dashed #e5e7eb;border-radius:10px;color:#64748b;background:#fafafa}
@media (min-width:768px){.rel-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}}
CSS);

/* @var $this yii\web\View */
/* @var $model app\models\Card */

$this->title = $model->card_code;
$this->params['breadcrumbs'][] = ['label' => 'Danh sách thẻ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="card-view mb-8">

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

    <?php
      $today   = new DateTime('today');
      $expired = $model->expired_at && (new DateTime($model->expired_at) < $today);
      $statusHtml = Html::tag(
          'span',
          $expired ? 'Hết hạn' : 'Còn hiệu lực',
          ['class' => 'badge ' . ($expired ? 'bg-danger' : 'bg-success')]
      );
    ?>

    <?= DetailView::widget([
        'model' => $model,
        'template' => '<tr><th style="width:260px;vertical-align:top;white-space:nowrap;">{label}</th><td>{value}</td></tr>',
        'attributes' => [
            // 'id',
            [
              'label'  => 'Trạng thái',
              'format' => 'raw',
              'value'  => $statusHtml,
            ],
            'card_code',
            'value:decimal',
            [
              'attribute' => 'used_value',
              'format'    => 'decimal',
              'label'     => 'Đã sử dụng',
            ],
            'remaining_value:decimal',
            'expired_at:date',
            
            [
              'attribute'=>'referral.code',
              'label'=>'Mã giới thiệu',
              'value'=> function($m){
                  return $m->referral ? $m->referral->code . ' ' . Html::a('Xem', ['referral/view', 'id' => $m->referral->id],['target'=>'_blank']) : null;
              },
              'format'=>'raw',
            ],
            'created_at:date',
            'updated_at:date',
            [
              'attribute'=>'createdBy.username',
              'label'=>'Người tạo',
            ],
            [
              'attribute'=>'updatedBy.username',
              'label'=>'Người cập nhật',
            ],
        ],
    ]) ?>

  <div class="rel-grid">
    

    <!-- Đối tác -->
    <div class="rel-box">
      <div> <strong class="rel-title">Đối tác áp dụng </strong><?= Html::a('<i>Xem danh sách</i>', ['partner/index'], ['target'=>'_blank']) ?></div>
      <?php if ($model->partners): ?>
        <div class="chips">
          <?php foreach ($model->partners as $p): ?>
            <?= Html::a(Html::encode($p->name), ['partner/view', 'id' => $p->id], ['class' => 'chip chip-secondary']) ?>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-hint">Chưa gắn đối tác nào.</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Dịch vụ -->
    <div class="rel-box">
     <div> <strong class="rel-title">Dịch vụ áp dụng </strong><?= Html::a('<i>Xem danh sách</i>', ['service/index'], ['target'=>'_blank']) ?></div>
      <?php if ($model->services): ?>
        <div class="chips">
          <?php foreach ($model->services as $s): ?>
            <?= Html::a(Html::encode($s->name), ['service/view', 'id' => $s->id], ['class' => 'chip chip-info']) ?>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-hint">Chưa gắn dịch vụ nào.</div>
      <?php endif; ?>
    </div>

</div>

<div style="display:flex;gap:16px;align-items:center;margin:30px 0;">
  <img src="<?= Html::encode($qrImg) ?>" style="width:160px;height:160px;object-fit:contain;border:1px solid #eee;padding:6px;border-radius:6px;background:#fff">
  <div>
    <div style="margin-bottom:8px"><strong>Link công khai:</strong> <?= Html::a($pubUrl,$pubUrl,['target'=>'_blank','rel'=>'nofollow']) ?></div>
    <div class="btns" style="display:flex;gap:8px;flex-wrap:wrap">
      <a class="btn btn-primary" href="<?= Html::encode($qrDl) ?>">Tải QR PNG</a>
      <a class="btn btn-default" href="<?= Html::encode($pdfDl) ?>">Tải PDF in thẻ</a>
      <a class="btn btn-info" href="<?= Html::encode($pubUrl) ?>" target="_blank">Xem công khai</a>
    </div>
  </div>
</div>

<hr style="margin:16px 0">
<?php
$canUpdate   = Yii::$app->user->can('manageCard');
$canDelete   = Yii::$app->user->can('admin');
$showActions = $canUpdate || $canDelete; // có cột Thao tác khi có quyền
?>
<div class="card-usage-history">
  <?php if (Yii::$app->user->can('manageCard')): ?>
    <?= Html::a('Ghi sử dụng', ['card-usage/create','card_id'=>$model->id], ['class'=>'btn btn-success']) ?>
  <?php endif; ?>

  <h3 class="mt-3">Lịch sử sử dụng</h3>

  <?php if ($model->usages): ?>
    <table class="table table-bordered table-sm align-middle">
      <thead>
        <tr>
          <th style="width:120px">Ngày</th>
          <th style="width:140px">Số tiền</th>
          <th style="width:220px">Đối tác</th>
          <th>Dịch vụ</th>
          <th>Ghi chú</th>
          <?php if ($showActions): ?><th style="width:160px">Thao tác</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($model->usages as $u): ?>
        <tr>
          <td><?= Yii::$app->formatter->asDate($u->used_at, 'php:d/m/Y') ?></td>
          <td><?= Yii::$app->formatter->asDecimal($u->amount, 0) ?> đ</td>
          <td><?= $u->partner ? Html::encode($u->partner->name) : '—' ?></td>
          <td>
            <?php if ($u->services): ?>
              <?php foreach ($u->services as $s): ?>
                <span class="badge bg-light text-dark" style="border:1px solid #ddd;">
                  <?= Html::encode($s->name) ?>
                </span>
              <?php endforeach; ?>
            <?php else: ?>—<?php endif; ?>
          </td>
          <td><?= nl2br(Html::encode((string)$u->note)) ?></td>

          <?php if ($showActions): ?>
          <td>
            <div class="btn-group btn-group-sm" role="group">
              <?= Html::a('Xem', ['card-usage/view','id'=>$u->id], ['class'=>'btn btn-outline-secondary']) ?>
              <?php if ($canUpdate): ?>
                <?= Html::a('Sửa', ['card-usage/update','id'=>$u->id], ['class'=>'btn btn-outline-primary']) ?>
              <?php endif; ?>
              <?php if ($canDelete): ?>
                <?= Html::a('Xoá', ['card-usage/delete','id'=>$u->id], [
                      'class'=>'btn btn-outline-danger',
                      'data' => [
                          'confirm' => 'Xoá ghi sử dụng này? Thao tác không thể hoàn tác.',
                          'method'  => 'post',
                      ],
                ]) ?>
              <?php endif; ?>
            </div>
          </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-secondary">Chưa có lịch sử sử dụng.</div>
  <?php endif; ?>
</div>

