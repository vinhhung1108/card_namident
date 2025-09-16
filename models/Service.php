<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "service".
 *
 * @property string $id
 * @property string $name
 * @property string $description
 */
class Service extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'service';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên dịch vụ',
            'description' => 'Mô tả',
        ];
    }


    public function getPartners()
    {
        return $this->hasMany(Partner::class, ['id' => 'partner_id'])
            ->viaTable('{{%partner_service}}', ['service_id' => 'id']);
    }

    public function getPartnersAvailable()
    {
        // bảng pivot: partner_service(partner_id, service_id)
        return $this->hasMany(\app\models\Partner::class, ['id' => 'partner_id'])
            ->viaTable('{{%partner_service}}', ['service_id' => 'id']);
    }
}
