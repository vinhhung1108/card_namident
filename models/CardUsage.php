<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\helpers\ArrayHelper;


class CardUsage extends ActiveRecord
{
    public $serviceIds = [];     // chọn nhiều dịch vụ
    public $used_at_ui;          // dd/mm/yyyy (tuỳ chọn)

    public static function tableName(){ return '{{%card_usage}}'; }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
        ];
    }

    public function rules()
{
    return [
        [['card_id','amount'], 'required'],

        [['card_id','partner_id'], 'integer'],

        // Lọc số tiền: giữ lại chữ số, ép int
        ['amount', 'filter', 'filter' => function($v){
            return (int)preg_replace('/\D+/', '', (string)$v);
        }, 'skipOnEmpty' => false],

        // Số nguyên >= 0 (đổi thành 1 nếu bạn muốn > 0)
        ['amount', 'integer', 'min' => 0,
            'message'  => 'Số tiền sử dụng phải là số nguyên.',
            'tooSmall' => 'Số tiền sử dụng phải lớn hơn hoặc bằng 0.',
        ],

        // Không vượt quá còn lại (theo delta khi update)
        ['amount', 'validateAmountRemain'],

        ['note', 'string', 'max' => 255],

        // partner/services có thể trống (điều chỉnh thủ công)
        ['partner_id', 'default', 'value' => null],
        [['serviceIds'], 'each', 'rule' => ['integer']],
        ['serviceIds', 'default', 'value' => []],
        ['serviceIds', 'validateServicesAgainstPartner'],

       // Ngày hết hạn & select lists
        ['used_at', 'filter', 'filter' => fn($v) => ($v === '' ? null : $v)],
        ['used_at', 'match', 'pattern' => '/^\d{4}-\d{2}-\d{2}$/', 'skipOnEmpty' => true],
        ['used_at', 'default', 'value' => date('Y-m-d')],
        ['used_at_ui', 'filter', 'filter' => fn($v) => $v === '' ? null : trim($v)],
        ['used_at_ui', 'match', 'pattern' => '/^\d{1,2}\/\d{1,2}\/\d{4}$/', 'skipOnEmpty' => true,
            'message' => 'Ngày sử dụng phải theo định dạng dd/mm/yyyy.'],

        // So với hạn thẻ
        ['used_at', 'validateUsedAtVsExpiry'],

        // Chỉ cho chọn partner/dịch vụ có trong thẻ
        ['partner_id', 'validatePartnerInCard'],
        ['serviceIds', 'validateServicesInCard'],
    ];
}

    public function attributeLabels()
    {
        return [
            'card_id'    => 'Thẻ',
            'amount'     => 'Số tiền sử dụng',
            'partner_id' => 'Sử dụng tại (Đối tác)',
            'serviceIds' => 'Dịch vụ sử dụng',
            'note'       => 'Ghi chú',
            'used_at_ui' => 'Ngày sử dụng',
            'used_at'    => 'Ngày sử dụng',
        ];
    }

   public function beforeValidate()
    {
        if (!parent::beforeValidate()) return false;

        // Parse used_at_ui -> used_at (Y-m-d), chấp nhận 2 hoặc 4 số năm
       $ui = trim((string)$this->used_at_ui);
        if ($ui !== '') {
            $dt = \DateTime::createFromFormat('!d/m/Y', $ui);
            $ok = $dt && $dt->format('d/m/Y') === $ui;
            if (!$ok) {
                $this->addError('used_at_ui','Ngày sử dụng không hợp lệ (dd/mm/yyyy).');
                // không cần return false; để các rule khác vẫn chạy nếu muốn
                // nhưng nếu bạn muốn dừng sớm thì giữ return false cũng không sao
            } else {
                $this->used_at = $dt->format('Y-m-d');
            }
        } else {
            $this->used_at = null;
        }

        return true;
    }

    // Quan hệ
    public function getCard(){ return $this->hasOne(Card::class, ['id'=>'card_id']); }
    public function getPartner(){ return $this->hasOne(Partner::class, ['id'=>'partner_id']); }
    // public function getCardUsageServices(){ return $this->hasMany(CardUsageService::class, ['usage_id'=>'id']); }
    public function getUsageServices(){ return $this->hasMany(CardUsageService::class, ['usage_id'=>'id']); }
    public function getServices(){ return $this->hasMany(Service::class, ['id'=>'service_id'])->via('usageServices'); }

    /* ===== Allowed lists ===== */
    public function getAllowedPartnerIds(): array
    {
        if (!$this->card) return [];
        return ArrayHelper::getColumn($this->card->partners, 'id');
    }
    public function getAllowedServiceIds(): array
    {
        if (!$this->card) return [];
        return ArrayHelper::getColumn($this->card->services, 'id');
    }

    /* ===== Validators ===== */
    public function validatePartnerInCard($attr)
      {
          if ($this->$attr === null || $this->$attr === '') return; // cho phép trống
          $allowed = $this->getAllowedPartnerIds();
          if (!$allowed || !in_array((int)$this->$attr, $allowed, true)) {
              $this->addError($attr, 'Chỉ được chọn đối tác đã gắn với thẻ, hoặc để trống.');
          }
      }

      public function validateServicesInCard($attr)
      {
          $ids = array_map('intval', (array)$this->$attr);
          if (!$ids) return;
          $allowed = $this->getAllowedServiceIds();
          $diff = array_diff($ids, $allowed);
          if ($diff) {
              $this->addError($attr, 'Chỉ được chọn dịch vụ đã gắn với thẻ (hoặc bỏ trống).');
          }
      }

    public function validateAmountRemain($attr)
    {
        if (!$this->card) return;
        // khi UPDATE: so delta, không phải so trực tiếp
        $old = $this->isNewRecord ? 0 : (int)$this->getOldAttribute('amount');
        $delta = (int)$this->$attr - $old;
        if ($delta <= 0) return; // giảm/không đổi -> ok

        $remain = (int)$this->card->remaining_value;
        if ($delta > $remain) {
            $this->addError($attr, 'Số tiền sử dụng vượt quá số tiền còn lại của thẻ.');
        }
    }

    /* ===== Sau lưu: đồng bộ pivot + cập nhật thẻ ===== */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Lấy amount cũ một cách đáng tin cậy
        $oldAmount = $insert ? 0 : (int)($changedAttributes['amount'] ?? 0);
        $newAmount = (int)$this->amount;
        $delta     = $newAmount - $oldAmount;

        if ($delta !== 0 && $this->card_id) {
            // Tăng/giảm used_value theo delta (atomic)
            \Yii::$app->db->createCommand()->update('{{%card}}', [
                // remaining_value = GREATEST(value - (used_value + delta), 0)
                'remaining_value' => new \yii\db\Expression('GREATEST(value - (used_value + :d), 0)'),
                'used_value'      => new \yii\db\Expression('used_value + :d'),
                'updated_at'      => time(),
            ], ['id' => $this->card_id])
            ->bindValue(':d', $delta)
            ->execute();
        }

        // Pivot services giữ nguyên như bạn đã có
        $current = \yii\helpers\ArrayHelper::getColumn($this->usageServices, 'service_id');
        $new     = array_map('intval', (array)$this->serviceIds);
        $toAdd   = array_diff($new, $current);
        $toDel   = array_diff($current, $new);

        if ($toDel) CardUsageService::deleteAll(['usage_id'=>$this->id, 'service_id'=>$toDel]);
        foreach ($toAdd as $sid) {
            (new CardUsageService(['usage_id'=>$this->id,'service_id'=>$sid]))->save(false);
        }
    }


    public function afterDelete()
    {
        parent::afterDelete();
        // hoàn lại tiền đã dùng khi xoá usage
        if ($this->card) {
            $card = $this->card;
            $card->used_value      = max(0, (int)$card->used_value - (int)$this->amount);
            $card->remaining_value = max(0, (int)$card->value - (int)$card->used_value);
            $card->save(false, ['used_value','remaining_value','updated_at']);
        }
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->used_at_ui = $this->used_at ? \Yii::$app->formatter->asDate($this->used_at, 'php:d/m/Y') : null;
        $this->serviceIds = array_column($this->usageServices, 'service_id');
    }

    /** Ngày sử dụng không được sau ngày hết hạn thẻ (nếu thẻ có hạn) */
    public function validateUsedAtVsExpiry($attr)
    {
        if (!$this->used_at || !$this->card || !$this->card->expired_at) return;

        $used = new \DateTime($this->used_at);
        $exp  = new \DateTime($this->card->expired_at);

        if ($used > $exp) {
            // Báo lỗi ngay trên field UI
            $this->addError('used_at_ui', 'Ngày sử dụng phải nhỏ hơn hoặc bằng ngày hết hạn của thẻ ('.$exp->format('d/m/Y').').');
        }
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL, // insert/update/delete đều chạy trong transaction
        ];
    }

    public function validateServicesAgainstPartner($attr)
    {
        $ids = array_map('intval', (array)$this->$attr);
        if (!$ids || !$this->partner_id) return;

        // dịch vụ của partner:
        $allowedByPartner = $this->partner ? 
            \yii\helpers\ArrayHelper::getColumn($this->partner->services, 'id') : [];
        $diff = array_diff($ids, $allowedByPartner);
        if ($diff) {
            $this->addError($attr, 'Các dịch vụ đã chọn không khả dụng tại đối tác đã chọn.');
        }
    }

}
