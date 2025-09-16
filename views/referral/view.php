<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use DateTime;
use yii\data\ActiveDataProvider;


/* @var $this yii\web\View */
/* @var $model app\models\Referral */

$this->title = $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Danh sách mã giới thiệu', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="referral-view">

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

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            // 'id',
            'code',
            'description:ntext',
        ],
    ]) ?>

</div>

<?php
// DataProvider lấy các thẻ gắn mã này
$cardsProvider = new ActiveDataProvider([
    'query' => $model->getCards(),
    'pagination' => ['pageSize' => 20],
    'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
]);
?>

<h3 class="mt-4">Danh sách thẻ phát hành</h3>

    <?= GridView::widget([
        'dataProvider' => $cardsProvider,
        'emptyText' => 'Chưa có thẻ nào gắn với mã này.',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'card_code',
                'label' => 'Mã thẻ',
                'format' => 'raw',
                'value' => function($c){
                    return Html::a(Html::encode($c->card_code), ['card/view', 'id' => $c->id], ['target' => '_blank']);
                }
            ],
            [
                'attribute' => 'value',
                'label' => 'Giá trị',
                'format' => ['decimal', 0],
            ],
            [
                'attribute' => 'used_value',
                'label' => 'Đã sử dụng',
                'format' => ['decimal', 0],
            ],
            [
                'attribute' => 'remaining_value',
                'label' => 'Còn lại',
                'format' => ['decimal', 0],
            ],
            [
                'attribute' => 'expired_at',
                'label' => 'Hết hạn',
                'format' => ['date', 'php:d/m/Y'],
            ],
            [
                'label' => 'Trạng thái',
                'format' => 'raw',
                'value' => function($c){
                    $expired = $c->expired_at && (new DateTime($c->expired_at)) < new DateTime('today');
                    return Html::tag('span', $expired ? 'Hết hạn' : 'Còn hiệu lực',
                        ['class' => 'badge '.($expired ? 'bg-danger' : 'bg-success')]
                    );
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'controller' => 'card',
                'template' => '{view} {update}',
                'urlCreator' => function($action, $c) {
                    return ['card/'.$action, 'id' => $c->id];
                }
            ],
        ],
    ]) ?>

</div>
