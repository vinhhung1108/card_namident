<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Service;
use app\models\Partner;
use app\models\Referral;
use yii\widgets\MaskedInput;
use yii\jui\DatePicker;
use yii\helpers\Json;

$serviceList  = ArrayHelper::map(Service::find()->orderBy('name')->all(), 'id', 'name');
$partnerList  = ArrayHelper::map(Partner::find()->orderBy('name')->all(), 'id', 'name');
$referralList = ArrayHelper::map(Referral::find()->orderBy('code')->all(), 'id', 'code');

$this->registerCss(<<<CSS
.card-form-wrap{max-width:1100px;margin:0 auto}
.card-form-wrap .row{margin-left:-8px;margin-right:-8px}
.card-form-wrap [class*="col-"]{padding-left:8px;padding-right:8px}
.card-form-wrap .form-group{margin-bottom:12px}
.card-form-wrap .listbox{height:260px;overflow:auto}
.card-form-actions{position:sticky;bottom:0;background:#fff;padding:8px 0;margin-top:8px;border-top:1px solid #eee}
@media (max-width: 767px){.card-form-wrap{padding:0 10px}.card-form-wrap .listbox{height:200px}}
CSS);

$this->registerJs(<<<JS
function toNum(s){ if(!s) return 0; return parseInt(String(s).replace(/[^0-9]/g,''))||0; }
function format(v){
  return (v||0).toString().replace(/\\B(?=(\\d{3})+(?!\\d))/g, ",");
}
function calcRemain(){
  var v  = toNum($('#card-value').val());
  var uv = toNum($('#card-used_value').val());
  var r  = Math.max(0, v - uv);
  $('#remain-view').val(format(r));
}
$(document).on('keyup change', '#card-value, #card-used_value', calcRemain);
calcRemain();
JS);

// Map partner -> list service ids
$partnerServiceMap = [];
foreach (Partner::find()->with('services')->all() as $p) {
    $partnerServiceMap[$p->id] = \yii\helpers\ArrayHelper::getColumn($p->services, 'id');
}
$allServices = \yii\helpers\ArrayHelper::map(Service::find()->orderBy('name')->all(),'id','name');

// Allowed ban đầu theo các partner đang có trên model
$allowedIds = [];
foreach ((array)$model->partnerIds as $pid) {
    $allowedIds = array_merge($allowedIds, $partnerServiceMap[$pid] ?? []);
}
$allowedIds = array_values(array_unique($allowedIds));
$serviceListForCard = $allowedIds ? array_intersect_key($allServices, array_flip($allowedIds)) : [];

$this->registerJs('
var psMap = '.Json::encode($partnerServiceMap).';
var allServices = '.Json::encode($allServices).';

function rebuildServiceOptions(){
  var selPartners = $("#card-partnerids").val() || [];
  var allowSet = new Set();
  selPartners.forEach(function(pid){
    (psMap[pid]||[]).forEach(function(sid){ allowSet.add(String(sid)); });
  });

  var $svc = $("#card-serviceids");
  var selected = $svc.val() || [];
  $svc.empty();

  Array.from(allowSet).sort(function(a,b){
    return (allServices[a]||"").localeCompare(allServices[b]||"");
  }).forEach(function(sid){
    if(allServices[sid]){
      var opt = $("<option>").val(sid).text(allServices[sid]);
      if (selected.indexOf(sid) >= 0) opt.prop("selected", true);
      $svc.append(opt);
    }
  });
}

$(document).on("change", "#card-partnerids", rebuildServiceOptions);
');

?>
<div class="card-form-wrap">
  <?php $form = ActiveForm::begin(); ?>

  <div class="row">
    <div class="col-sm-6 col-lg-4">
      <?php if ($model->isNewRecord): ?>
        <?= $form->field($model,'card_code')->textInput(['maxlength'=>true]) ?>
      <?php else: ?>
        <?= $form->field($model,'card_code')->textInput([
              'readonly'  => true,
              'tabindex'  => -1,
              'class'     => 'form-control-plaintext', // Bootstrap 5
              'aria-readonly' => 'true',
          ])->hint('Mã thẻ không thể thay đổi sau khi tạo.') ?>
      <?php endif; ?>
    </div>
    <div class="col-sm-6 col-lg-4"><?= $form->field($model,'value')->widget(MaskedInput::class, [
              'clientOptions' => [
                  'alias' => 'decimal',
                  'groupSeparator' => ',',
                  'autoGroup' => true,
                  'digits' => 0,
                  'digitsOptional' => false,
                  'rightAlign' => false,     // canh trái khi gõ
                  'removeMaskOnSubmit' => true, // gửi số sạch
                  'autoUnmask' => true,      // <-- THÊM DÒNG NÀY
              ],
          ]) ?></div>
    <div class="col-sm-6 col-lg-4">
      <?= $form->field($model,'used_value')
          ->textInput([
              'value'    => Yii::$app->formatter->asDecimal((int)$model->used_value, 0),
              'disabled' => true,            // KHÓA không cho nhập
              'id'       => 'card-used_value'
          ])
          ->hint('Trường này chỉ thay đổi khi ghi Lịch sử sử dụng thẻ.') ?>
    </div>

  </div>

  <div class="row">
    <div class="col-sm-6 col-lg-4">
      <?= $form->field($model, 'expired_at_ui')->widget(DatePicker::class, [
            'options' => [
                'class' => 'form-control',
                'autocomplete' => 'off',
                'placeholder' => 'dd/mm/yyyy',
            ],
            'dateFormat' => 'dd/MM/yyyy', // jQuery UI format (yy = 4 chữ số năm)
            'clientOptions' => [
                'changeMonth' => true,
                'changeYear'  => true,
                'yearRange'   => '1900:+20',
                'showButtonPanel' => true,
            ],
        ]) ?>
    </div>
    <div class="col-sm-6 col-lg-8"><?= $form->field($model,'referral_id')->dropDownList($referralList,['prompt'=>'-- Không chọn --']) ?></div>
  </div>

  <div class="row">
    
    <div class="col-md-6">
      <?= $form->field($model,'partnerIds')->listBox($partnerList,['multiple'=>true,'size'=>12,'class'=>'form-control listbox']) ?>
    </div>

    <div class="col-md-6">
      <?= $form->field($model,'serviceIds')->listBox(
          $serviceListForCard,
          ['multiple'=>true,'size'=>12,'class'=>'form-control listbox','id'=>'card-serviceids']
      )->hint('Chỉ hiện dịch vụ khả dụng theo các đối tác đã chọn.') ?>
    </div>
  </div>

  <div class="card-form-actions text-right">
    <?= Html::submitButton($model->isNewRecord ? 'Tạo' : 'Lưu', ['class'=>'btn btn-success']) ?>
  </div>
  <?php ActiveForm::end(); ?>
</div>
