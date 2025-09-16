<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);

$iconWeb = Yii::getAlias('@web/images/credit-card.png');
$iconFs  = Yii::getAlias('@webroot/images/credit-card.png');
$ver     = @filemtime($iconFs) ?: time();

$this->registerLinkTag([
    'rel'  => 'icon',
    'type' => 'image/png',
    'href' => $iconWeb . '?v=' . $ver, // tránh cache cũ
]);
$this->registerLinkTag([
    'rel'  => 'apple-touch-icon',
    'href' => $iconWeb . '?v=' . $ver,
]);

$this->registerCss(<<<CSS
:root{ --sidebar-w: 240px; }
body.layout-has-sidebar { background:#fff; }

@media (min-width: 768px){
  body.layout-has-sidebar { padding-left: var(--sidebar-w); }
  .sidebar {
    position: fixed; left:0; top:0; bottom:0; width: var(--sidebar-w);
    background: #0d6efd; color:#fff; border-right:1px solid rgba(255,255,255,.15);
    display:flex; flex-direction:column; overflow-y:auto;
  }
  .sidebar .brand{display:flex; align-items:center; gap:10px; padding:14px 16px; font-weight:600;}
  .sidebar .brand img{height:22px;}
  .sidebar .nav.flex-column{padding:6px 6px 12px;}
  .sidebar .nav-link{color:#fff; border-radius:.5rem; margin:2px 8px; padding:.5rem .75rem;}
  .sidebar .nav-link.active, .sidebar .nav-link:hover{background:rgba(255,255,255,.15);}
  .sidebar .section-title{opacity:.9;font-size:.75rem;letter-spacing:.04em;text-transform:uppercase;padding:8px 16px;margin-top:6px}
  .sidebar .bottom{margin-top:auto; padding:12px 10px;}
  .sidebar .bottom .btn-logout{width:100%; text-align:left;}
}
CSS);
// $this->registerCss('.dropdown-menu-end{right:0;left:auto}'); // canh phải menu dropdown
?>


<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100 layout-has-sidebar">
<?php $this->beginBody() ?>

<header id="header">
<?php
// Xây menu theo quyền
$menu = [
    ['label' => 'Trang chủ', 'url' => ['/site/index']],
];
if (!Yii::$app->user->isGuest) {
    $menu[] = ['label' => 'Danh sách thẻ', 'url' => ['/card/index']];
    if (Yii::$app->user->can('manageService'))  $menu[] = ['label' => 'Dịch vụ', 'url' => ['/service/index']];
    if (Yii::$app->user->can('managePartner'))  $menu[] = ['label' => 'Đối tác', 'url' => ['/partner/index']];
    if (Yii::$app->user->can('manageReferral')) $menu[] = ['label' => 'Mã giới thiệu', 'url' => ['/referral/index']];
    if (Yii::$app->user->can('admin'))          $menu[] = ['label' => 'Người dùng', 'url' => ['/user/index']];
}

// Nút login / khu vực tài khoản
if (Yii::$app->user->isGuest) {
    $accountLinks = [
        ['label' => 'Đăng nhập', 'url' => ['/site/login']],
    ];
} else {
    $accountLinks = [
        ['label' => 'Đổi mật khẩu', 'url' => ['/user/change-password']],
        // logout (POST)
        '<li class="nav-item">
            '.Html::beginForm(['/site/logout']).'
            '.Html::submitButton(
                'Đăng xuất ('.Html::encode(Yii::$app->user->identity->username).')',
                ['class' => 'dropdown-item btn-logout']
            ).'
            '.Html::endForm().'
        </li>',
    ];
}
?>

<!-- Top bar cho MOBILE: chỉ hiện trên màn hình nhỏ để mở offcanvas -->
<nav class="navbar navbar-dark bg-primary d-md-none">
  <div class="container-fluid">
    <nav class="navbar navbar-dark bg-primary d-md-none">
      <div class="container-fluid">
        <button class="navbar-toggler" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar"
                aria-controls="offcanvasSidebar" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand ms-2" href="<?= Yii::$app->homeUrl ?>">
          <img src="<?= Yii::getAlias('@web/images/credit-card.png') ?>" style="height:22px;vertical-align:-4px;margin-right:6px">
          Namident Cards
        </a>
      </div>
    </nav>
    <!-- <button class="btn btn-outline-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
      ☰
    </button> -->
    <!-- <a class="navbar-brand ms-2" href="<?= Yii::$app->homeUrl ?>">
      <img src="<?= Yii::getAlias('@web/images/credit-card.png') ?>" style="height:22px;vertical-align:-4px;margin-right:6px">
      Namident Cards
    </a> -->
  </div>
</nav>

<!-- OFFCANVAS sidebar cho MOBILE -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSidebar">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Namident Cards</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-0">
    <?= Nav::widget([
        'options' => ['class' => 'nav flex-column'],
        'items'   => $menu,
        'encodeLabels' => false,
    ]) ?>
    <hr class="my-2">
    <?= Nav::widget([
        'options' => ['class' => 'nav flex-column'],
        'items'   => $accountLinks,
        'encodeLabels' => false,
    ]) ?>
  </div>
</div>

<!-- SIDEBAR cố định cho DESKTOP -->
<aside class="sidebar d-none d-md-flex">
  <div class="brand">
    <img src="<?= Yii::getAlias('@web/images/credit-card.png') ?>" alt="">
    <span>Namident Cards</span>
  </div>
  <div class="section-title">Menu</div>
  <?= Nav::widget([
      'options' => ['class' => 'nav flex-column'],
      'items'   => $menu,
      'encodeLabels' => false,
  ]) ?>
  <div class="section-title">Tài khoản</div>
  <?= Nav::widget([
      'options' => ['class' => 'nav flex-column'],
      'items'   => $accountLinks,
      'encodeLabels' => false,
  ]) ?>
  <div class="bottom small opacity-75 ps-2 pe-2">
    &copy; Namident <?= date('Y') ?>
  </div>
</aside>
</header>

<main id="main" class="flex-shrink-0" role="main">
  <div class="container pt-3">
    <?php if (!empty($this->params['breadcrumbs'])): ?>
      <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
    <?php endif ?>
    <?= Alert::widget() ?>
    <?= $content ?>
  </div>
</main>

<footer id="footer" class="mt-auto py-3 bg-light">
    <div class="container">
        <div class="row text-muted">
            <div class="col-md-6 text-center text-md-start">&copy; Namident <?= date('Y') ?></div>
            <div class="col-md-6 text-center text-md-end"></div>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
