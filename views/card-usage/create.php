<?php
/** @var $card app\models\Card */
/** @var $model app\models\CardUsage */
/** @var $serviceList array */
/** @var $partnerList array */

use yii\helpers\Html;

$this->title = 'Ghi sử dụng thẻ: '.$card->card_code;
$this->params['breadcrumbs'][] = ['label'=>'Thẻ', 'url'=>['/card/index']];
$this->params['breadcrumbs'][] = ['label'=>$card->card_code, 'url'=>['/card/view','id'=>$card->id]];
$this->params['breadcrumbs'][] = 'Ghi sử dụng';
?>
<div class="card-usage-create">
  <h1><?= Html::encode($this->title) ?></h1>
  <p>Số dư còn lại hiện tại: <strong><?= Yii::$app->formatter->asDecimal($card->remaining_value,0) ?> đ</strong></p>
  <?= $this->render('_form', compact('card','model','serviceList','partnerList','partnerServiceMap')) ?>
</div>
