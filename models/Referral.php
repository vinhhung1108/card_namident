<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "referral".
 *
 * @property string $id
 * @property string $code
 * @property string $description
 */
class Referral extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'referral';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code'], 'required'],
            [['description'], 'string'],
            [['code'], 'string', 'max' => 50],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Mã giới thiệu',
            'description' => 'Mô tả',
        ];
    }

    public function getCards()
    {
        return $this->hasMany(\app\models\Card::class, ['referral_id' => 'id'])
            ->orderBy(['id' => SORT_DESC]);
    }
}
