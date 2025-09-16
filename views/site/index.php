<?php

/** @var yii\web\View $this */

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <div class="jumbotron text-center bg-transparent mt-5 mb-5">
        <h1 class="display-4">Quản lý thẻ quà tặng</h1>

        <p class="lead">Cung cấp, quản lý thẻ quà tặng cho khách hàng.</p>

        <p><a class="btn btn-lg btn-success" href="<?= \yii\helpers\Url::to(['card/index']) ?>">Danh sách thẻ quà tặng</a></p>
    </div>

    <div class="body-content">

        <div class="row">
            <div class="col-lg-4 mb-3">
                <h2>Dịch vụ áp dụng</h2>

                <p>Thêm mới, chỉnh sửa, quản lý các dịch vụ có thể được áp dụng chính sách tiêu dùng trên giá trị của thẻ.</p>

                <p><a class="btn btn-outline-secondary" href="<?= \yii\helpers\Url::to(['service/index']) ?>">Danh sách dịch vụ &raquo;</a></p>
            </div>
            <div class="col-lg-4 mb-3">
                <h2>Đối tác</h2>

                <p>Thêm mới, chỉnh sửa, quản lý danh sách các đối tác có thể được áp dụng dịch vụ.</p>

                <p><a class="btn btn-outline-secondary" href="<?= \yii\helpers\Url::to(['partner/index']) ?>">Danh sách đối tác &raquo;</a></p>
            </div>
            <div class="col-lg-4">
                <h2>Nguồn giới thiệu</h2>

                <p>Quản lý các nguồn giới thiệu phân phối thẻ đến khách hàng.</p>

                <p><a class="btn btn-outline-secondary" href="<?= \yii\helpers\Url::to(['referral/index']) ?>">Danh sách giới thiệu &raquo;</a></p>
            </div>
        </div>

    </div>
</div>
