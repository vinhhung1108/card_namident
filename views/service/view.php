<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Service */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Danh sách dịch vụ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

/** ====== CSS nhẹ cho danh sách đối tác ====== */
$this->registerCss(<<<CSS
.partner-list{display:flex;flex-direction:column;gap:10px;margin-top:8px}
.partner-item{background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:12px}
.partner-name{font-weight:700;color:#0f172a;margin-bottom:6px}
.partner-meta{display:grid;grid-template-columns:1fr;gap:4px}
.partner-meta .label{color:#64748b;min-width:60px;display:inline-block;margin-right:2px}
.partner-meta .value a{text-decoration:none}
.partner-meta .value a:hover{text-decoration:underline}
@media(min-width:700px){.partner-meta{grid-template-columns:1fr 1fr}}
.empty-hint{padding:10px 12px;border:1px dashed #e5e7eb;border-radius:10px;color:#64748b;background:#fafafa;margin-top:8px}
CSS);

/** Helpers nhỏ */
$fmt = fn($v) => $v ? Html::encode($v) : '—';
$tel = function($p){
    if (!$p) return '—';
    $plain = preg_replace('/\D+/', '', $p);
    return Html::a(Html::encode($p), 'tel:' . $plain);
};

// Lấy danh sách đối tác áp dụng qua quan hệ $model->partnersAvailable
$partners = $model->partnersAvailable ?? [];
?>
<div class="service-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => ['confirm' => 'Bạn chắc chắn muốn xóa mục này?', 'method' => 'post'],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'name',
            'description:ntext',
        ],
        'template' => '<tr><th style="width:220px">{label}</th><td>{value}</td></tr>',
    ]) ?>

    <h3 style="margin-top:18px">Đối tác áp dụng</h3>
    <?php if ($partners): ?>
        <div class="partner-list">
            <?php foreach ($partners as $p): ?>
                <div class="partner-item">
                    <div class="partner-name">
                        <?= Html::a($fmt($p->name), ['partner/view', 'id' => $p->id], ['target' => '_blank']) ?>
                    </div>
                    <div class="partner-meta">
                        <div><span class="label">Địa chỉ:</span> <span class="value"><?= $fmt($p->address ?? null) ?></span></div>
                        <div><span class="label">Điện thoại:</span> <span class="value"><?= $tel($p->phone ?? null) ?></span></div>
                        <?php if (!empty($p->email)): ?>
                            <div><span class="label">Email:</span> <span class="value"><?= Html::mailto(Html::encode($p->email)) ?></span></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-hint">Chưa có đối tác nào áp dụng dịch vụ này.</div>
    <?php endif; ?>

</div>
