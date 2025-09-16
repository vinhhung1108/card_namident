<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\jui\DatePicker;
use yii\widgets\MaskedInput;

/* @var $model app\models\CardUsage */
/* @var $partnerList array [id=>name] */
/* @var $serviceList array [id=>name] */
/* @var $partnerServiceMap array [partner_id => [service_id,...]] */
?>

<div class="card-form-wrap" style="max-width:800px;margin:0 auto">
  <?php $form = ActiveForm::begin(['enableClientValidation' => false]); ?>

  <div class="row">
    <div class="col-sm-6">
      <?= $form->field($model, 'amount', ['enableClientValidation'=>false])
          ->widget(MaskedInput::class, [
              'clientOptions' => [
                  'alias'             => 'decimal',
                  'groupSeparator'    => ',',
                  'autoGroup'         => true,
                  'digits'            => 0,
                  'digitsOptional'    => false,
                  'rightAlign'        => false,
                  'removeMaskOnSubmit'=> true,
                  'autoUnmask'        => true,
              ],
          ])->hint('Số tiền sử dụng (không vượt quá số dư còn lại).') ?>
    </div>

    <div class="col-sm-6">
      <?= $form->field($model, 'used_at_ui')->widget(DatePicker::class, [
            'options' => [
                'class' => 'form-control',
                'autocomplete' => 'off',
                'placeholder' => 'dd/mm/yyyy',
            ],
            'dateFormat' => 'dd/MM/yyyy',
            'clientOptions' => [
                'changeMonth' => true,
                'changeYear'  => true,
                'yearRange'   => '1900:+20',
                'showButtonPanel' => true,
            ],
        ]) ?>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-6">
      <?= $form->field($model, 'partner_id')
          ->dropDownList($partnerList, ['prompt' => '-- Không chọn --'])
          ->hint('Chọn đối tác để lọc danh sách dịch vụ bên cạnh (có thể để trống).') ?>
    </div>

    <div class="col-sm-6">
      <?php
      // dựng danh sách checkbox với wrapper có data-sid để dễ ẩn/hiện
      echo $form->field($model, 'serviceIds')->checkboxList(
          $serviceList,
          [
              'item' => function($index, $label, $name, $checked, $value){
                  $id = htmlspecialchars($value, ENT_QUOTES);
                  $lbl = htmlspecialchars($label, ENT_QUOTES);
                  return '<label class="svc-item" data-sid="'.$id.'" style="display:inline-block;margin:0 12px 6px 0">'
                       . Html::checkbox($name, $checked, ['value'=>$id])
                       . ' ' . $lbl . '</label>';
              },
          ]
      )->hint('Chỉ hiển thị các dịch vụ khả dụng của đối tác đã chọn.');
      ?>
    </div>
  </div>

  <?= $form->field($model, 'note')->textarea(['rows'=>3]) ?>

  <div class="card-form-actions text-right" style="position:sticky;bottom:0;background:#fff;padding:8px 0;margin-top:8px;border-top:1px solid #eee">
    <?= Html::submitButton($model->isNewRecord ? 'Ghi sử dụng' : 'Cập nhật', ['class'=>'btn btn-primary']) ?>
  </div>

  <?php ActiveForm::end(); ?>
</div>

<?php
// ==== JS lọc dịch vụ theo đối tác =====
$mapJson = json_encode($partnerServiceMap ?: [], JSON_UNESCAPED_UNICODE);
$js = <<<JS
(function($){
  var psMap = $mapJson;  // {partner_id: [service_id,...]}
  var \$partner = $('#cardusage-partner_id');
  var \$svcWrap = $('#cardusage-serviceids'); // wrapper field
  function showAllowedServices(){
    var pid = parseInt(\$partner.val() || 0, 10);
    var allowed = psMap[pid] || []; // [] = không hiển thị dịch vụ nào (khi chọn đối tác không có dịch vụ chung với thẻ), null sẽ hiển thị tất cả
    // Duyệt qua từng item
    \$svcWrap.find('.svc-item').each(function(){
      var sid = parseInt($(this).data('sid'), 10);
      if (!allowed) {
        $(this).show();
      } else {
        var ok = allowed.indexOf(sid) !== -1;
        if (ok) {
          $(this).show();
        } else {
          // ẩn và bỏ chọn nếu đang chọn
          $(this).hide();
          $(this).find('input[type=checkbox]').prop('checked', false);
        }
      }
    });
  }
  // chạy khi đổi đối tác
  \$partner.on('change', showAllowedServices);
  // chạy lần đầu (trường hợp vào trang update có sẵn đối tác)
  showAllowedServices();
})(jQuery);
JS;
$this->registerJs($js);
?>
