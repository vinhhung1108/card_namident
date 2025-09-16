<?php
use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\Card|null $card */

$this->title = $card ? ('Lịch sử sử dụng - Thẻ: ' . Html::encode($card->card_code)) : 'Lịch sử sử dụng thẻ';
$this->params['breadcrumbs'][] = ['label' => 'Danh sách thẻ', 'url' => ['/card/index']];
if ($card) $this->params['breadcrumbs'][] = ['label' => $card->card_code, 'url' => ['/card/view', 'id'=>$card->id]];
$this->params['breadcrumbs'][] = 'Lịch sử sử dụng';

$fmtMoney = function($n){ return Yii::$app->formatter->asDecimal((int)$n, 0) . ' đ'; };
$fmtDate  = function($d){ return $d ? Yii::$app->formatter->asDate($d, 'php:d/m/Y') : '—'; };

?>
<div class="card-usage-index">

  <div class="d-flex align-items-center justify-content-between mb-2">
    <h1 class="h4 m-0"><?= Html::encode($this->title) ?></h1>
    <div class="btn-group">
      <?php if ($card): ?>
        <?= Html::a('Quay lại thẻ', ['/card/view','id'=>$card->id], ['class'=>'btn btn-outline-secondary']) ?>
        <?php if (Yii::$app->user->can('manageCard')): ?>
          <?= Html::a('Ghi sử dụng', ['create','card_id'=>$card->id], ['class'=>'btn btn-primary']) ?>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <?= GridView::widget([
      'dataProvider' => $dataProvider,
      'tableOptions' => ['class'=>'table table-striped table-bordered align-middle'],
      'columns' => [
          ['class' => 'yii\grid\SerialColumn'],

          // Nếu không lọc theo thẻ, hiển thị cột thẻ
          !$card ? [
              'label' => 'Thẻ',
              'value' => function($m){
                  return $m->card ? $m->card->card_code : '—';
              },
          ] : false,

          [
              'attribute' => 'used_at',
              'label' => 'Ngày sử dụng',
              'value' => fn($m) => $fmtDate($m->used_at),
          ],
          [
              'attribute' => 'amount',
              'label' => 'Số tiền',
              'value' => fn($m) => $fmtMoney($m->amount),
          ],
          [
              'attribute' => 'partner_id',
              'label' => 'Đối tác',
              'value' => function($m){ return $m->partner ? $m->partner->name : '—'; },
          ],
          [
              'label' => 'Dịch vụ',
              'format' => 'raw',
              'value' => function($m){
                  if (!$m->services) return '—';
                  $names = array_map(fn($s)=>Html::encode($s->name), $m->services);
                  return implode(', ', $names);
              }
          ],
          [
              'attribute' => 'note',
              'label' => 'Ghi chú',
              'value' => fn($m) => $m->note ?: '—',
          ],

          [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{view} {update} {delete}',
              'urlCreator' => function ($action, $model, $key, $index) {
                  return [$action, 'id' => $model->id];
              },
              'visibleButtons' => [
                  'update' => fn($m) => Yii::$app->user->can('manageCard'),
                  'delete' => fn($m) => Yii::$app->user->can('admin'),
              ],
          ],
      ],
  ]); ?>
</div>
