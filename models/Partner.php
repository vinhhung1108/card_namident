<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "partner".
 *
 * @property string $id
 * @property string $name
 * @property string $address
 * @property string $phone
 * @property string $email
 * @property string $note
 */
class Partner extends \yii\db\ActiveRecord
{

      public $serviceIds = []; // danh sách dịch vụ khả dụng

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'partner';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['note'], 'string'],
            [['name', 'address'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 50],
            [['email'], 'string', 'max' => 100],
            [['email'], 'email'],
             // services khả dụng
            ['serviceIds', 'each', 'rule' => ['integer']],
            ['serviceIds', 'default', 'value' => []],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Đối tác',
            'address' => 'Địa chỉ',
            'phone' => 'Điện thoại',
            'email' => 'Email',
            'note' => 'Ghi chú',
            'serviceIds' => 'Dịch vụ khả dụng',

        ];
    }

    // Quan hệ: đối tác - dịch vụ (qua bảng partner_service)
    public function getServices()
    {
        return $this->hasMany(Service::class, ['id' => 'service_id'])
            ->viaTable('{{%partner_service}}', ['partner_id' => 'id'])
            ->orderBy(['name'=>SORT_ASC]);
    }

    public function getPartnerServices()
    {
        return $this->hasMany(PartnerService::class, ['partner_id' => 'id']);
    }

    public function getServicesAvailable()
    {
        return $this->hasMany(Service::class, ['id' => 'service_id'])->via('partnerServices');
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->serviceIds = ArrayHelper::getColumn($this->services, 'id');
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // đồng bộ pivot partner_service
        $current = (new \yii\db\Query())
            ->select('service_id')->from('{{%partner_service}}')
            ->where(['partner_id'=>$this->id])->column();

        $new   = array_map('intval', (array)$this->serviceIds);
        $toAdd = array_diff($new, $current);
        $toDel = array_diff($current, $new);

        if ($toDel) {
            \Yii::$app->db->createCommand()
                ->delete('{{%partner_service}}', ['partner_id'=>$this->id, 'service_id'=>$toDel])
                ->execute();
        }
        foreach ($toAdd as $sid) {
            \Yii::$app->db->createCommand()
                ->insert('{{%partner_service}}', ['partner_id'=>$this->id,'service_id'=>$sid])
                ->execute();
        }
    }
}
