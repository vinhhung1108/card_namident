<?php
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\CardUsage $model */
/** @var app\models\Card $card */
/** @var array $partnerList */
/** @var array $serviceList */

$this->title = 'Sửa ghi sử dụng';
$this->params['breadcrumbs'][] = ['label' => 'Danh sách thẻ', 'url' => ['/card/index']];
$this->params['breadcrumbs'][] = ['label' => $card->card_code, 'url' => ['/card/view','id'=>$card->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="card-usage-update">
  <div class="d-flex align-items-center justify-content-between mb-2">
    <h1 class="h4 m-0"><?= Html::encode($this->title) ?> <small class="text-muted">— Thẻ: <?= Html::encode($card->card_code) ?></small></h1>
    <div>
      <?= Html::a('Quay lại thẻ', ['/card/view','id'=>$card->id], ['class'=>'btn btn-outline-secondary']) ?>
    </div>
  </div>

  <?= $this->render('_form', [
      'model'       => $model,
      'card'        => $card,
      'partnerList' => $partnerList,
      'serviceList' => $serviceList,
      'partnerServiceMap'  => $partnerServiceMap,
  ]) ?>
</div>
