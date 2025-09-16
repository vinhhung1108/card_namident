<?php
namespace app\models;

use yii\db\ActiveRecord;

class CardUsageService extends ActiveRecord
{
    public static function tableName(){ return '{{%card_usage_service}}'; }
    public function rules(){
        return [
            [['usage_id','service_id'],'required'],
            [['usage_id','service_id'],'integer'],
        ];
    }
}
