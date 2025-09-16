<?php
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Người dùng';
$this->params['breadcrumbs'][] = $this->title;

$statusMap = [10 => 'Active', 0 => 'Inactive'];
?>
<div class="user-index">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="m-0"><?= Html::encode($this->title) ?></h1>
        <div class="btn-group">
            <?= Html::a('Tạo người dùng', ['create'], ['class' => 'btn btn-success']) ?>
            <?= Html::a('Bộ lọc nâng cao', ['index'], ['class' => 'btn btn-outline-secondary', 'data-bs-toggle'=>'collapse','data-bs-target'=>'#advSearch']) ?>
        </div>
    </div>

    <div id="advSearch" class="collapse mb-3">
        <?= $this->render('_search', ['model' => $searchModel]) ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel, // lọc nhanh theo cột
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            [
                'attribute' => 'username',
                'contentOptions' => ['style'=>'white-space:nowrap'],
            ],
            [
                'attribute' => 'email',
                'format' => 'email',
            ],
            [
                'attribute' => 'full_name',
                'label' => 'Họ tên',
            ],
            [
                'attribute' => 'status',
                'filter'    => $statusMap,
                'value'     => function($m) use ($statusMap){ return $statusMap[$m->status] ?? $m->status; },
                'contentOptions' => ['style'=>'white-space:nowrap'],
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Tạo lúc',
                'format' => ['datetime','php:d/m/Y H:i'],
                'filter' => false, // lọc theo form nâng cao
                'contentOptions' => ['style'=>'white-space:nowrap'],
            ],
            [
                'attribute' => 'updated_at',
                'label' => 'Cập nhật',
                'format' => ['datetime','php:d/m/Y H:i'],
                'filter' => false,
                'contentOptions' => ['style'=>'white-space:nowrap'],
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Thao tác',
                'contentOptions' => ['style'=>'white-space:nowrap'],
            ],
        ],
    ]); ?>

</div>
