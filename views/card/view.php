<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\ArrayHelper;


use yii\helpers\Url;
$qrImg  = Url::to(['card/qr','code'=>$model->card_code], true);
$qrDl   = Url::to(['card/qr','code'=>$model->card_code,'download'=>1], true);
$pdfDl  = Url::to(['card/qr-pdf','code'=>$model->card_code], true);
$pubUrl = Url::to(['card/show','code'=>$model->card_code], true);

/* CSS nhẹ nhàng cho chip */
$this->registerCss(<<<CSS
.rel-box{margin-top:10px}
.rel-title{margin:0 0 8px;font-size:16px;font-weight:600}
.chip:hover{background:#eef2ff;border-color:#c7d2fe}
.chip-info{background:#ecfeff;border-color:#a5f3fc;color:#075985}
.chip-info:hover{background:#cffafe;border-color:#67e8f9}
.chip-secondary{background:#f1f5f9;border-color:#cbd5e1;color:#0f172a}
.chip-secondary:hover{background:#e2e8f0}
.empty-hint{padding:10px 12px;border:1px dashed #e5e7eb;border-radius:10px;color:#64748b;background:#fafafa}
@media (min-width:768px){.rel-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}}
CSS);

// Chips & partner card (giống show)
$this->registerCss(<<<CSS
.partner-list{display:flex;flex-direction:column;gap:10px}
.partner-item{background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:12px}
.partner-name{font-weight:700;color:#0f172a;margin-bottom:6px}
.partner-meta{display:grid;grid-template-columns:1fr;gap:4px}
.partner-meta .label{color:#64748b;min-width:50px;display:inline-block;margin-right:2px}
.partner-meta .value a{text-decoration:none}
.partner-meta .value a:hover{text-decoration:underline}
@media(min-width:700px){.partner-meta{grid-template-columns:1fr 1fr}}

.chips{display:flex;flex-wrap:wrap;gap:8px}
.card-view .chip{
  padding:8px 14px;border-radius:999px;font-size:13px;font-weight:600;line-height:1;
  white-space:nowrap;border:1px solid transparent;transition:transform .08s ease,box-shadow .18s ease
}
.card-view .chip:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(2,6,23,.10)}
.card-view .chip-indigo {background:linear-gradient(180deg,#eef2ff,#e0e7ff);color:#1e3a8a;border-color:#c7d2fe}
.card-view .chip-cyan   {background:linear-gradient(180deg,#ecfeff,#cffafe);color:#155e75;border-color:#67e8f9}
.card-view .chip-emerald{background:linear-gradient(180deg,#ecfdf5,#d1fae5);color:#065f46;border-color:#6ee7b7}
.card-view .chip-amber  {background:linear-gradient(180deg,#fffbeb,#fde68a);color:#92400e;border-color:#fcd34d}
.card-view .chip-rose   {background:linear-gradient(180deg,#fff1f2,#ffe4e6);color:#9f1239;border-color:#fecdd3}
.card-view .chip-violet {background:linear-gradient(180deg,#f5f3ff,#ede9fe);color:#5b21b6;border-color:#ddd6fe}
@media print{ .card-view .chip{background:#fff;border-color:#bbb;color:#111;box-shadow:none} }
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
              'attribute' => 'issue_price',
              'format'    => ['decimal', 0],
              'label'     => 'Giá phát hành',
            ],
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

  <?php
      // Lấy danh sách service id của thẻ
      $cardServiceIds = array_map('intval', ArrayHelper::getColumn($model->services, 'id'));
      // Bảng màu cho chip
      $palette = ['indigo','cyan','emerald','amber','rose','violet'];

      // Helper hiển thị an toàn + tel link
      $fmt = fn($v) => $v ? Html::encode($v) : '—';
      $tel = function($p){
          if (!$p) return '—';
          $plain = preg_replace('/\D+/', '', $p);
          return Html::a(Html::encode($p), 'tel:' . $plain);
      };
  ?>

  <div class="section pb-4" style="margin-top:16px">
    <h3 style="font-size:16px;margin:0 0 8px">Đối tác & dịch vụ áp dụng</h3>

    <?php if ($model->partners): ?>
      <div class="partner-list">
        <?php foreach ($model->partners as $p): ?>
          <div class="partner-item pb-2">
            <div class="partner-name"><?= $fmt($p->name) ?></div>
            <div class="partner-meta">
              <div><span class="label">Địa chỉ:</span> <span class="value"><?= $fmt($p->address ?? $p->dia_chi ?? null) ?></span></div>
              <div><span class="label">Điện thoại:</span> <span class="value"><?= $tel($p->phone ?? $p->so_dien_thoai ?? null) ?></span></div>
            </div>

            <?php
              // Dịch vụ khả dụng của đối tác này AND thuộc danh sách dịch vụ của thẻ
              $availServices = $p->servicesAvailable ?? [];
              $showServices  = [];
              foreach ($availServices as $sv) {
                  if (in_array((int)$sv->id, $cardServiceIds, true)) {
                      $showServices[] = $sv;
                  }
              }
            ?>

            <div class="section" style="margin-top:10px">
              <div class="text-muted" style="margin-bottom:6px">Dịch vụ áp dụng:</div>
              <?php if ($showServices): ?>
                <div class="chips">
                  <?php foreach ($showServices as $sv): ?>
                    <?php $cls = 'chip chip-' . $palette[$sv->id % count($palette)]; ?>
                    <span class="<?= $cls ?>"><?= Html::encode($sv->name) ?></span>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="alert alert-secondary mb-0">Đối tác này chưa có dịch vụ áp dụng trong thẻ.</div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-secondary">Chưa có đối tác áp dụng.</div>
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

