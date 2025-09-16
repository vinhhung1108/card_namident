<?php
use app\assets\AppAsset;
use yii\helpers\Html;

AppAsset::register($this);
$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name'=>'viewport','content'=>'width=device-width, initial-scale=1']);

// Không index trang public (thông tin nhạy cảm)
$this->registerMetaTag(['name'=>'robots','content'=>'noindex, nofollow']);

// Màu thanh trình duyệt (Android) / PWA
$this->registerMetaTag(['name'=>'theme-color','content'=>'#0ea5e9']);

// Open Graph cơ bản (tuỳ chọn)
$ogTitle = $this->title ?: 'Namident Cards';
$ogDesc  = 'Thông tin thẻ khách hàng Namident.';
$ogUrl   = Yii::$app->request->absoluteUrl;
$iconWeb = Yii::getAlias('@web/images/credit-card.png');
$iconFs  = Yii::getAlias('@webroot/images/credit-card.png');
$ver     = @filemtime($iconFs) ?: time();

$this->registerMetaTag(['property'=>'og:title','content'=>$ogTitle]);
$this->registerMetaTag(['property'=>'og:description','content'=>$ogDesc]);
$this->registerMetaTag(['property'=>'og:url','content'=>$ogUrl]);
$this->registerMetaTag(['property'=>'og:image','content'=>$iconWeb.'?v='.$ver]);

$this->registerLinkTag(['rel'=>'icon','type'=>'image/png','href'=>$iconWeb.'?v='.$ver]);
$this->registerLinkTag(['rel'=>'apple-touch-icon','href'=>$iconWeb.'?v='.$ver]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
  <title><?= Html::encode($this->title) ?></title>
  <?php $this->head() ?>
  <style>
    /* Nền và font */
    body{
      background:#f8fafc;
      font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
      color:#0f172a;
      /* Safe area cho điện thoại có tai thỏ */
      padding-left: env(safe-area-inset-left);
      padding-right: env(safe-area-inset-right);
      padding-top: env(safe-area-inset-top);
      padding-bottom: env(safe-area-inset-bottom);
    }
    .public-wrap{min-height:100dvh;display:flex;align-items:flex-start;justify-content:center;padding:16px}
    .card-page{
      width:100%;max-width:900px;background:#fff;border-radius:16px;
      box-shadow:0 8px 28px rgba(2,6,23,.08);padding:20px
    }
    @media(min-width:768px){.card-page{padding:28px}}

    .muted{color:#64748b}
    .pill{display:inline-flex;align-items:center;gap:8px;border-radius:999px;padding:6px 10px;font-size:13px;font-weight:600}
    .pill.ok{background:#ecfeff;color:#075985;border:1px solid #a5f3fc}
    .pill.warn{background:#fffbeb;color:#92400e;border:1px solid #fde68a}  /* thêm trạng thái warn */
    .pill.bad{background:#fee2e2;color:#7f1d1d;border:1px solid #fecaca}

    .grid{display:grid;grid-template-columns:1fr;gap:14px}
    @media(min-width:700px){.grid{grid-template-columns:1fr 1fr}}
    .kv{background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:12px}
    .kv .k{font-size:12px;color:#64748b;margin-bottom:2px}
    .kv .v{font-weight:600}

    .section{margin-top:18px}
    .section h3{font-size:16px;margin:0 0 8px}

    .chips{display:flex;flex-wrap:wrap;gap:8px}
    .chip{padding:6px 10px;border-radius:999px;border:1px solid #e5e7eb;background:#f1f5f9;font-size:13px}
    .empty{padding:10px;border:1px dashed #e5e7eb;border-radius:10px;background:#fafafa;color:#64748b}

    .bar{height:10px;background:#e5e7eb;border-radius:999px;overflow:hidden}
    .bar>div{height:100%;background:linear-gradient(90deg,#06b6d4,#3b82f6)}

    .panel{display:grid;grid-template-columns:1fr;gap:14px}
    @media(min-width:820px){.panel{grid-template-columns:1.2fr .8fr;gap:18px}}

    .qrbox{border:1px solid #e5e7eb;border-radius:12px;padding:12px;background:#fafafa;text-align:center}
    .qrbox img{width:140px;height:140px;object-fit:contain;background:#fff;border:1px solid #eee;border-radius:8px;padding:6px}
    .link{word-break:break-all}

    /* Dark mode nhẹ (tuỳ chọn) */
    @media (prefers-color-scheme: dark){
      body{background:#0b1220;color:#e5e7eb}
      .card-page{background:#0f172a;box-shadow:0 8px 28px rgba(0,0,0,.35)}
      .kv{background:#0b1220;border-color:#22314d}
      .chip{background:#0b1220;border-color:#22314d}
      .empty{background:#0b1220;border-color:#22314d;color:#93a4c3}
      .qrbox{background:#0b1220;border-color:#22314d}
    }

    /* In ấn */
    @media print{
      .public-wrap{padding:0}
      .card-page{box-shadow:none;border-radius:0}
    }
  </style>
</head>
<body>
<?php $this->beginBody() ?>
  <main class="public-wrap">
    <div class="card-page">
      <?= $content ?>
    </div>
  </main>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
