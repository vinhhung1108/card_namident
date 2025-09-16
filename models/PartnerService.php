<?php
namespace app\models;

use yii\db\ActiveRecord;

class PartnerService extends ActiveRecord
{
    public static function tableName(){ return '{{%partner_service}}'; }
    public static function primaryKey(){ return ['partner_id','service_id']; }
}