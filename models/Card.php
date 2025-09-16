<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\helpers\ArrayHelper;

class Card extends ActiveRecord
{
    public $expired_at_ui; // dùng cho form
    public $serviceIds = [];
    public $partnerIds = [];

    public static function tableName(){ return '{{%card}}'; }

    public function behaviors()
    {
        return [
            // updated_at/created_at dạng int
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
            // updated_by/created_by
            [
                'class' => BlameableBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by', 'updated_by'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_by'],
                ],
                // nếu không có user (console) thì để null
                'defaultValue' => null,
            ],
        ];
    }

    public function rules()
{
    return [
        [['card_code'], 'required', 'message' => 'Mã thẻ không được để trống.'],
        [['card_code'], 'string', 'max' => 50],
        [['card_code'], 'unique', 'message' => 'Mã thẻ này đã tồn tại.'],
        ['card_code', 'filter', 'filter' => 'trim'],

        ['issue_price', 'default', 'value' => 0],

        // ----- SANITIZE / CAST -----
        ['issue_price', 'filter', 'filter' => function($v){
            if ($v === '' || $v === null) return 0;
            return (int)preg_replace('/[^\d]/', '', (string)$v);
        }],

        // Kiểu số
        [['issue_price'], 'integer', 'min' => 0],
        
        [['referral_id'], 'integer'],
        ['referral_id', 'exist', 'skipOnError' => true,
            'targetClass' => Referral::class, 'targetAttribute' => ['referral_id' => 'id']],


        // ----- DEFAULTS -----
        ['value', 'default', 'value' => 0],
        ['used_value', 'default', 'value' => 0],
        ['remaining_value', 'default', 'value' => function($m){
            return is_numeric($m->value) ? (int)$m->value : 0;
        }],

        // ----- SANITIZE / CAST (đặt trước compare) -----
        ['value', 'filter', 'filter' => function($v){
            if ($v === '' || $v === null) return 0;
            return (int)preg_replace('/[^\d]/', '', (string)$v);
        }],
        ['used_value', 'filter', 'filter' => function($v){
            if ($v === '' || $v === null) return 0;
            return (int)preg_replace('/[^\d]/', '', (string)$v);
        }],
        ['remaining_value', 'filter', 'filter' => function($v){
            if ($v === '' || $v === null) return null;
            return (int)preg_replace('/[^\d]/', '', (string)$v);
        }],

        // Kiểu số (đặt SAU các filter)
        [['value','remaining_value','used_value'], 'integer', 'min' => 0, 'skipOnEmpty' => true],

        // ----- RÀNG BUỘC -----
        ['value', 'compare',
            'compareAttribute' => 'used_value',
            'operator' => '>=',
            'type' => 'number',
            'message' => 'Giá trị phải ≥ Đã sử dụng.'
        ],

        // Kiểm tra: dịch vụ phải thuộc các đối tác đã chọn trên form
        ['serviceIds', 'validateServicesAllowedByPartners'],
        
        // remaining_value ≤ value (vẫn giữ, nhưng thực tế remaining_value do bạn tự tính nên luôn đúng)
        ['remaining_value', 'compare', 'compareAttribute' => 'value',
            'operator' => '<=', 'type' => 'number',
            'message' => 'Giá trị còn lại phải ≤ Giá trị.'],

        // Ngày hết hạn & select lists
        ['expired_at', 'filter', 'filter' => fn($v) => ($v === '' ? null : $v)],
        ['expired_at', 'match', 'pattern' => '/^\d{4}-\d{2}-\d{2}$/', 'skipOnEmpty' => true],

        [['serviceIds','partnerIds'], 'each', 'rule' => ['integer']],
        [['serviceIds','partnerIds'], 'default', 'value' => []],

        ['expired_at_ui', 'filter', 'filter' => fn($v) => $v === '' ? null : trim($v)],
        ['expired_at_ui', 'match', 'pattern' => '/^\d{1,2}\/\d{1,2}\/\d{4}$/', 'skipOnEmpty' => true,
            'message' => 'Ngày hết hạn phải theo định dạng dd/mm/yyyy.'],
    ];
}

    public function attributeLabels()
    {
        return [
            'card_code' => 'Mã thẻ',
            'value' => 'Giá trị (VND)',
            'remaining_value' => 'Còn lại (VND)',
            'expired_at' => 'Ngày hết hạn',
            'referral_id' => 'Mã giới thiệu',
            'serviceIds' => 'Dịch vụ áp dụng',
            'partnerIds' => 'Đối tác áp dụng',
            'created_at' => 'Thời gian tạo',
            'updated_at' => 'Thời gian cập nhật',
            'created_by' => 'Người tạo',
            'updated_by' => 'Người cập nhật',
            'expired_at_ui' => 'Ngày hết hạn',
            'used_value'     => 'Đã sử dụng',
            'issue_price' => 'Giá phát hành (VND)',

        ];
    }

    // quan hệ
    public function getReferral(){ return $this->hasOne(Referral::class, ['id'=>'referral_id']); }
    public function getCardServices(){ return $this->hasMany(CardService::class, ['card_id'=>'id']); }
    public function getServices(){ return $this->hasMany(Service::class, ['id'=>'service_id'])->via('cardServices'); }
    public function getCardPartners(){ return $this->hasMany(CardPartner::class, ['card_id'=>'id']); }
    public function getPartners(){ return $this->hasMany(Partner::class, ['id'=>'partner_id'])->via('cardPartners'); }
    public function getCreatedBy(){ return $this->hasOne(User::class, ['id'=>'created_by']); }
    public function getUpdatedBy(){ return $this->hasOne(User::class, ['id'=>'updated_by']); }

    public function afterFind()
    {
        parent::afterFind();
        $this->serviceIds = ArrayHelper::getColumn($this->services, 'id');
        $this->partnerIds = ArrayHelper::getColumn($this->partners, 'id');

        $this->expired_at_ui = $this->expired_at
            ? \Yii::$app->formatter->asDate($this->expired_at, 'php:d/m/Y')
            : null;
        // tự tính used_value nếu chưa có (dành cho bản cũ nâng cấp)
        if ($this->used_value === null) {
        $this->used_value = max(0, (int)$this->value - (int)$this->remaining_value);
    }
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) return false;

        // ====== convert UI date (dd/mm/yyyy) -> DB (Y-m-d) một cách an toàn ======
        // Cho phép bỏ trống
        $ui = trim((string)$this->expired_at_ui);
        if ($ui === '') {
            $this->expired_at = null;
        } else {
            // '!' để reset time về 00:00:00, tránh kế thừa field trước đó
            $dt = \DateTime::createFromFormat('!d/m/Y', $ui);
            $errors = \DateTime::getLastErrors();
            $hasParseError = is_array($errors) && (
                (($errors['error_count'] ?? 0) > 0) ||
                (($errors['warning_count'] ?? 0) > 0)
            );

            // So khớp round-trip: format lại phải y hệt input (tránh 32/13/2025 ...)
            $validRoundTrip = $dt && $dt->format('d/m/Y') === $ui;

            if ($dt === false || $hasParseError || !$validRoundTrip) {
                $this->addError('expired_at_ui', 'Ngày hết hạn không hợp lệ (định dạng dd/mm/yyyy).');
                return false;
            }
            $this->expired_at = $dt->format('Y-m-d');
        }

        // --- Tự tính remaining_value ---
        $v  = (int)$this->value;
        $uv = (int)$this->used_value;
        if ($uv < 0) $uv = 0;
        if ($v < 0)  $v  = 0;
        if ($uv > $v) {
            // để rule compare bắt lỗi; vẫn set remaining_value về 0 để tránh âm
            $this->remaining_value = 0;
        } else {
            $this->remaining_value = $v - $uv;
        }

        return true;
    }


    public function afterValidate()
    {
        parent::afterValidate();
        // nếu validate fail thì giữ lại giá trị UI để form hiển thị lại
        if ($this->hasErrors()) {
            if ($this->expired_at && !$this->expired_at_ui) {
                $this->expired_at_ui = \Yii::$app->formatter->asDate($this->expired_at, 'php:d/m/Y');
            }
        }
    }

    public function afterSave($insert, $changed)
    {
        parent::afterSave($insert, $changed);

        // DB -> UI để khi redirect về view/update vẫn thấy đúng format
        $this->expired_at_ui = $this->expired_at
            ? \Yii::$app->formatter->asDate($this->expired_at, 'php:d/m/Y')
            : null;
            
        $this->syncMany('service');
        $this->syncMany('partner');
    }

    private function syncMany($type)
    {
        if ($type === 'service') {
            $current = ArrayHelper::getColumn($this->cardServices, 'service_id');
            $new = array_map('intval', (array)$this->serviceIds);
            $toAdd = array_diff($new, $current);
            $toDel = array_diff($current, $new);
            if ($toDel) CardService::deleteAll(['card_id'=>$this->id,'service_id'=>$toDel]);
            foreach ($toAdd as $sid) (new CardService(['card_id'=>$this->id,'service_id'=>$sid]))->save(false);
        } else {
            $current = ArrayHelper::getColumn($this->cardPartners, 'partner_id');
            $new = array_map('intval', (array)$this->partnerIds);
            $toAdd = array_diff($new, $current);
            $toDel = array_diff($current, $new);
            if ($toDel) CardPartner::deleteAll(['card_id'=>$this->id,'partner_id'=>$toDel]);
            foreach ($toAdd as $pid) (new CardPartner(['card_id'=>$this->id,'partner_id'=>$pid]))->save(false);
        }
    }

    public function getValueVnd(){ return number_format((int)$this->value, 0, '.', ','); }
    public function getRemainingValueVnd(){ return number_format((int)$this->remaining_value, 0, ',', '.'); }
    public function getUsages()
    {
        return $this->hasMany(CardUsage::class, ['card_id'=>'id'])
            ->orderBy(['used_at'=>SORT_DESC,'id'=>SORT_DESC]);
    }

    public function scenarios()
    {
        $sc = parent::scenarios();
        // Các field cho form
        $common = ['value','issue_price','expired_at_ui','referral_id','serviceIds','partnerIds','used_value'];

        $sc['create'] = array_merge(['card_code'], $common); // tạo mới: có card_code
        $sc['update'] = $common;                              // cập nhật: KHÔNG có card_code
        return $sc;
    }

    public function getAllowedServiceIdsFromPartners(): array
    {
        $ids = [];
        foreach ($this->partners as $p) {
            $ids = array_merge($ids, \yii\helpers\ArrayHelper::getColumn($p->services, 'id'));
        }
        $ids = array_unique(array_map('intval', $ids));
        return $ids;
    }

    public function validateServicesAgainstPartners($attr)
    {
        $services = array_map('intval', (array)$this->$attr);
        if (!$services) return;

        $partnerIds = array_map('intval', (array)$this->partnerIds);
        if (!$partnerIds) {
            $this->addError($attr, 'Bạn cần chọn Đối tác trước khi chọn Dịch vụ.');
            return;
        }

        $allowed = $this->getAllowedServiceIdsFromPartners(); // union các service khả dụng
        $diff = array_diff($services, $allowed);
        if ($diff) {
            $this->addError($attr, 'Chỉ được chọn dịch vụ thuộc các đối tác đã gắn cho thẻ.');
        }
    }

    public function validateServicesAllowedByPartners($attr)
    {
        $services = array_map('intval', (array)$this->$attr);
        if (!$services) return; // không chọn dịch vụ nào => OK

        $partnerIds = array_map('intval', (array)$this->partnerIds);
        if (!$partnerIds) {
            $this->addError($attr, 'Vui lòng chọn Đối tác trước khi chọn Dịch vụ.');
            return;
        }

        // Lấy union các service_id khả dụng của tất cả partner đã chọn
        $allowed = (new \yii\db\Query())
            ->select('service_id')
            ->from('{{%partner_service}}')
            ->where(['partner_id' => $partnerIds])
            ->column();

        $allowed = array_unique(array_map('intval', $allowed));

        // Nếu đối tác chưa được cấu hình dịch vụ khả dụng, allowed rỗng
        if (!$allowed) {
            $this->addError($attr, 'Các đối tác đã chọn chưa được cấu hình dịch vụ khả dụng.');
            return;
        }

        $diff = array_diff($services, $allowed);
        if ($diff) {
            $this->addError($attr, 'Chỉ được chọn dịch vụ thuộc các đối tác đã gắn cho thẻ.');
        }
    }

}
