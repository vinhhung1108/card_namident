<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\CardUsage $model */

$this->title = 'Chi tiết sử dụng thẻ';
$this->params['breadcrumbs'][] = ['label'=>'Danh sách thẻ', 'url'=>['/card/index']];
if ($model->card) {
    $this->params['breadcrumbs'][] = ['label'=>$model->card->card_code, 'url'=>['/card/view','id'=>$model->card->id]];
}
$this->params['breadcrumbs'][] = $this->title;

$fmtMoney = fn($n)=>Yii::$app->formatter->asDecimal((int)$n,0).' đ';
$fmtDate  = fn($d)=>$d?Yii::$app->formatter->asDate($d,'php:d/m/Y'):'—';

$this->registerCss('.chip{display:inline-block;padding:4px 8px;border-radius:999px;background:#f1f5f9;border:1px solid #e5e7eb;margin:2px 6px 2px 0;font-size:12px}');
?>
<div class="card-usage-view">
  <div class="d-flex align-items-center justify-content-between mb-2">
    <h1 class="h4 m-0"><?= Html::encode($this->title) ?></h1>
    <div class="btn-group">
      <?php if ($model->card): ?>
        <?= Html::a('Quay lại thẻ', ['/card/view','id'=>$model->card->id], ['class'=>'btn btn-outline-secondary']) ?>
        <?php if (Yii::$app->user->can('manageCard')): ?>
          <?= Html::a('Sửa', ['update','id'=>$model->id], ['class'=>'btn btn-primary']) ?>
        <?php endif; ?>
        <?php if (Yii::$app->user->can('admin')): ?>
          <?= Html::a('Xoá', ['delete','id'=>$model->id], [
                'class'=>'btn btn-danger',
                'data'=>['confirm'=>'Xoá ghi sử dụng này?','method'=>'post']
          ]) ?>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <?= DetailView::widget([
      'model' => $model,
      'template' => '<tr><th style="width:240px">{label}</th><td>{value}</td></tr>',
      'attributes' => [
          [
              'label' => 'Thẻ',
              'format'=> 'raw',
              'value' => $model->card
                  ? Html::a(Html::encode($model->card->card_code), ['/card/view','id'=>$model->card->id], ['target'=>'_blank'])
                  : '—',
          ],
          [
              'attribute' => 'used_at',
              'label'     => 'Ngày sử dụng',
              'value'     => $fmtDate($model->used_at),
          ],
          [
              'attribute' => 'amount',
              'label'     => 'Số tiền sử dụng',
              'value'     => $fmtMoney($model->amount),
          ],
          [
              'attribute' => 'partner_id',
              'label'     => 'Sử dụng tại (Đối tác)',
              'value'     => $model->partner ? $model->partner->name : '—',
          ],
          [
              'label'  => 'Dịch vụ sử dụng',
              'format' => 'raw',
              'value'  => $model->services
                  ? implode('', array_map(fn($s)=>'<span class="chip">'.Html::encode($s->name).'</span>', $model->services))
                  : '—',
          ],
          [
              'attribute' => 'note',
              'label'     => 'Ghi chú',
              'format'    => 'ntext',
              'value'     => $model->note ?: '—',
          ],
          [
              'attribute' => 'created_at',
              'label'     => 'Tạo lúc',
              'value'     => $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at, 'php:d/m/Y H:i') : '—',
          ],
          [
              'attribute' => 'updated_at',
              'label'     => 'Cập nhật lúc',
              'value'     => $model->updated_at ? Yii::$app->formatter->asDatetime($model->updated_at, 'php:d/m/Y H:i') : '—',
          ],
      ],
  ]) ?>
</div>
