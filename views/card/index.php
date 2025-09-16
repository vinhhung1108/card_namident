<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\CardSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Danh sách thẻ';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="card-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
  <div class="mb-3">
    <div class="pull-left" style="margin-right:10px;">
        <?= Html::a('Thêm mới thẻ', ['create'], ['class' => 'btn btn-success']) ?>
        <?php // Html::a('Quản lý dịch vụ', ['service/index'], ['class' => 'btn btn-info', 'target'=>'_blank']) ?>
        <?php // Html::a('Quản lý đối tác', ['partner/index'], ['class' => 'btn btn-secondary', 'target'=>'_blank']) ?>
        <?php // Html::a('Quản lý mã giới thiệu', ['referral/index'], ['class' => 'btn btn-primary', 'target'=>'_blank']) ?>
    </div>
      <div class="clearfix"></div>
  </div>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
    'class' => 'yii\grid\SerialColumn',
],
'card_code',
[
    'attribute' => 'value',
    'label' => 'Giá trị (VND)',
    'value' => fn($m) => $m->valueVnd,
],
[
    'attribute' => 'remaining_value',
    'label' => 'Còn lại (VND)',
    'value' => fn($m) => $m->remainingValueVnd,
],
'expired_at:date',
[
    'attribute' => 'referral_id',
    'label' => 'Mã giới thiệu',
    'value' => fn($m) => $m->referral ? $m->referral->code : null,
],
[
    'label' => 'Dịch vụ',
    'format' => 'raw',
    'value' => function($m){
        $names = array_map(fn($s)=>$s->name, $m->services);
        return $names ? '<span class="badge bg-info">'.implode('</span> <span class="badge bg-info">', $names).'</span>' : '';
    }
],
[
    'label' => 'Đối tác',
    'format' => 'raw',
    'value' => function($m){
        $names = array_map(fn($p)=>$p->name, $m->partners);
        return $names ? '<span class="badge bg-secondary">'.implode('</span> <span class="badge bg-secondary">', $names).'</span>' : '';
    }
],
['class' => 'yii\grid\ActionColumn'],

        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
