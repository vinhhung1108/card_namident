<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

/** @var $model app\models\Card */
$this->title = 'Thông tin thẻ: ' . $model->card_code;

// Helpers
$money  = fn($n)=>Yii::$app->formatter->asDecimal((int)$n, 0).' đ';
$qrImg  = Url::to(['card/qr','code'=>$model->card_code], true);
$qrDl   = Url::to(['card/qr','code'=>$model->card_code,'download'=>1], true);
$pubUrl = Url::to(['card/show','code'=>$model->card_code], true);

// Tính trạng thái, % còn lại, ngày còn/hết
$today   = new DateTime('today');
$isExpired = $model->expired_at ? (new DateTime($model->expired_at) < $today) : false;
$percent = ($model->value > 0) ? max(0, min(100, round($model->remaining_value * 100 / $model->value))) : 0;

$daysText = '—';
$statusClass = 'ok';
$statusLabel = 'Còn hiệu lực';

if ($model->expired_at) {
    $exp = new DateTime($model->expired_at);
    $diff = $today->diff($exp)->days;
    if ($isExpired) {
        $daysText = $diff . ' ngày trước';
        $statusClass = 'bad';
        $statusLabel = 'Hết hạn';
    } else {
        $daysText = $diff . ' ngày nữa';
        if ($diff <= 7) { // sắp hết hạn trong 7 ngày
            $statusClass = 'warn';
            $statusLabel = 'Sắp hết hạn';
        }
    }
}

// CSS
$this->registerCss(<<<CSS
.card-public-wrap{max-width:840px;margin:16px auto;padding:16px}
.header-row{display:flex;align-items:center;justify-content:space-between;gap:12px}
.pill{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;font-weight:600;font-size:13px}
.pill.ok{background:#ecfdf5;color:#065f46}
.pill.warn{background:#fffbeb;color:#92400e}
.pill.bad{background:#fef2f2;color:#991b1b}
.muted{color:#64748b}

.panel{display:grid;grid-template-columns:1.2fr .8fr;gap:20px;align-items:start}
@media(max-width:767px){.panel{grid-template-columns:1fr}}

.grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
@media(max-width:767px){.grid{grid-template-columns:1fr}}

.kv{background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:12px}
.kv .k{color:#64748b;font-size:12px;margin-bottom:6px}
.kv .v{font-weight:600;color:#0f172a;white-space:nowrap}

.bar{height:10px;border-radius:999px;background:#f1f5f9;overflow:hidden;border:1px solid #e5e7eb}
.bar > div{height:100%;background:linear-gradient(90deg,#10b981,#06b6d4)}
.section{margin-top:16px}

.qrbox{border:1px solid #e5e7eb;border-radius:12px;padding:12px;text-align:center;background:#fff}
.qrbox img{width:180px;height:180px;object-fit:contain;border:1px solid #eee;padding:6px;border-radius:8px;background:#fff}
.qr-actions{display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-top:8px}

/* .chips{display:flex;flex-wrap:wrap;gap:8px}
.chip{display:inline-block;padding:6px 10px;border-radius:999px;font-size:13px;line-height:1;white-space:nowrap;border:1px solid #e5e7eb;background:#f8fafc;color:#0f172a} */
.empty{padding:10px 12px;border:1px dashed #e5e7eb;border-radius:10px;color:#64748b;background:#fafafa}

.footer-note{margin-top:18px;padding:10px;border:1px dashed #e5e7eb;border-radius:8px;background:#fafafa;font-size:12px;color:#64748b}

/* In ấn: chỉ nội dung chính, không màu nền đậm */
@media print{
  .qr-actions,.footer-note{display:none}
  .chip{border-color:#ddd;background:#fff}
  .kv{background:#fff}
}
CSS);

$this->registerCss(<<<CSS
.partner-list{display:flex;flex-direction:column;gap:10px}
.partner-item{background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:12px}
.partner-name{font-weight:700;color:#0f172a;margin-bottom:6px}
.partner-meta{display:grid;grid-template-columns:1fr;gap:4px}
.partner-meta .label{color:#64748b;min-width:50px;display:inline-block;margin-right:2px}
.partner-meta .value a{text-decoration:none}
.partner-meta .value a:hover{text-decoration:underline}
@media(min-width:700px){
  .partner-meta{grid-template-columns:1fr 1fr} /* desktop: chia 2 cột gọn mắt */
}
CSS);

$this->registerCss(<<<CSS
.card-public-wrap .chip{
  padding:8px 14px;
  border-radius:999px;
  font-size:13px;
  font-weight:600;
  line-height:1;
  white-space:nowrap;
  border:1px solid transparent;
  transition:transform .08s ease,box-shadow .18s ease;
}
.card-public-wrap .chip:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(2,6,23,.10)}
.card-public-wrap .chip-indigo {background:linear-gradient(180deg,#eef2ff,#e0e7ff);color:#1e3a8a;border-color:#c7d2fe}
.card-public-wrap .chip-cyan   {background:linear-gradient(180deg,#ecfeff,#cffafe);color:#155e75;border-color:#67e8f9}
.card-public-wrap .chip-emerald{background:linear-gradient(180deg,#ecfdf5,#d1fae5);color:#065f46;border-color:#6ee7b7}
.card-public-wrap .chip-amber  {background:linear-gradient(180deg,#fffbeb,#fde68a);color:#92400e;border-color:#fcd34d}
.card-public-wrap .chip-rose   {background:linear-gradient(180deg,#fff1f2,#ffe4e6);color:#9f1239;border-color:#fecdd3}
.card-public-wrap .chip-violet {background:linear-gradient(180deg,#f5f3ff,#ede9fe);color:#5b21b6;border-color:#ddd6fe}
@media print{ .card-public-wrap .chip{background:#fff;border-color:#bbb;color:#111;box-shadow:none} }
CSS);

// JS: copy link
$this->registerJs(<<<JS
document.getElementById('copy-link')?.addEventListener('click', async function(e){
  e.preventDefault();
  try{
    await navigator.clipboard.writeText(this.dataset.url);
    this.innerText = 'Đã sao chép';
    setTimeout(()=>{ this.innerText='Sao chép link'; }, 1200);
  }catch(err){ alert('Không sao chép được, hãy chọn và copy thủ công.'); }
});
JS);
?>

<div class="card-public-wrap">
  <!-- Header -->
  <div class="header-row">
    <h1 style="font-size:22px;margin:0">Thông tin thẻ</h1>
    <span class="pill <?= Html::encode($statusClass) ?>">
      <?= Html::encode($statusLabel) ?>
    </span>
  </div>
  <div class="muted" style="margin:6px 0 14px">
    Mã thẻ: <strong style="color:#0f172a"><?= Html::encode($model->card_code) ?></strong>
    <?php if ($model->expired_at): ?>
      <span class="muted" style="margin-left:8px">• Ngày hết hạn: <?= Html::encode($model->expired_at_ui) ?> (<?= Html::encode($daysText) ?>)</span>
    <?php endif; ?>
  </div>

  <!-- Tổng quan + QR -->
  <div class="panel">
    <div>
      <div class="grid">
        <div class="kv">
          <div class="k">Giá trị</div>
          <div class="v"><?= $money($model->value) ?></div>
        </div>
        <div class="kv">
          <div class="k">Đã sử dụng</div>
          <div class="v"><?= $money($model->used_value) ?></div>
        </div>
        <div class="kv">
          <div class="k">Giá trị còn lại</div>
          <div class="v"><?= $money($model->remaining_value) ?></div>
        </div>
        <div class="kv">
          <div class="k">Ngày hết hạn</div>
          <div class="v"><?= $model->expired_at_ui ?: '—' ?></div>
        </div>
      </div>

      <div class="section">
        <div class="k muted" style="margin-bottom:6px">Tỷ lệ còn lại</div>
        <div class="bar"><div style="width:<?= (int)$percent ?>%"></div></div>
        <div class="muted" style="margin-top:6px"><?= (int)$percent ?>%</div>
      </div>
    </div>

    <div>
      <div class="qrbox">
        <div class="muted" style="margin-bottom:8px">Quét QR để mở trang này</div>
        <a href="<?= Html::encode($pubUrl) ?>" target="_blank" rel="noopener">
          <img src="<?= Html::encode($qrImg) ?>" alt="QR - <?= Html::encode($model->card_code) ?>">
        </a>
        <div class="qr-actions">
          <a class="btn btn-sm btn-primary" href="<?= Html::encode($qrDl) ?>">Tải QR PNG</a>
          <a id="copy-link" class="btn btn-sm btn-outline-secondary" href="#" data-url="<?= Html::encode($pubUrl) ?>">Sao chép link</a>
          <a class="btn btn-sm btn-outline-primary" href="<?= Html::encode($pubUrl) ?>" target="_blank" rel="noopener">Mở trang</a>
        </div>
      </div>
    </div>
  </div>

<?php
  // Helpers nhỏ để hiển thị an toàn + tel link
    $fmt  = fn($v) => $v ? \yii\helpers\Html::encode($v) : '—';
    $tel  = function($p){
        if (!$p) return '—';
        $plain = preg_replace('/\D+/', '', $p);
        return \yii\helpers\Html::a(\yii\helpers\Html::encode($p), 'tel:' . $plain);
    };
?>
  <!-- Đối tác -->

  <?php

    $cardServiceIds = array_map('intval', ArrayHelper::getColumn($model->services, 'id'));
    
    $palette = ['indigo','cyan','emerald','amber','rose','violet'];

    $chip = fn($name) => '<span class="chip">'.Html::encode($name).'</span>';
  ?>
  <div class="section pb-4">
    <h3 style="font-size:16px;margin:0 0 8px">Đối tác áp dụng</h3>

    <?php if ($model->partners): ?>
      <div class="partner-list">
        <?php foreach ($model->partners as $p): ?>
          <div class="partner-item pb-4">
            <div class="partner-name"><?= $fmt($p->name) ?></div>
            <div class="partner-meta">
              <div><span class="label">Địa chỉ:</span> <span class="value"><?= $fmt($p->address ?? $p->dia_chi ?? null) ?></span></div>
              <div><span class="label">Điện thoại:</span> <span class="value"><?= $tel($p->phone ?? $p->so_dien_thoai ?? null) ?></span></div>
            </div>

            <?php
                // Lấy các dịch vụ khả dụng ở đối tác *và* nằm trong dịch vụ của thẻ
                $availServices = $p->servicesAvailable ?? [];
                $showServices  = [];
                foreach ($availServices as $sv) {
                    if (in_array((int)$sv->id, $cardServiceIds, true)) {
                        $showServices[] = $sv;
                    }
                }
              ?>
              <div class="section" style="margin-top:10px">
                <div class="k muted" style="margin-bottom:6px">Dịch vụ áp dụng: </div>
                <?php if ($showServices): ?>
                  <div class="chips">
                    <?php foreach ($showServices as $sv): ?>
                      <?php $cls = 'chip chip-' . $palette[$sv->id % count($palette)]; ?>
                      <span class="<?= $cls ?>"><?= Html::encode($sv->name) ?></span>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <div class="empty">Đối tác này chưa có dịch vụ áp dụng trong thẻ.</div>
                <?php endif; ?>
              </div>
              
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty">Chưa có đối tác áp dụng.</div>
    <?php endif; ?>
  </div>

  <div class="footer-note mt-4">
    Mẹo: lưu ảnh QR hoặc thêm trang này vào màn hình chính để truy cập nhanh hơn.
  </div>
</div>
