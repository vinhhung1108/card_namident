<?php
namespace app\models;
use yii\db\ActiveRecord;

class CardService extends ActiveRecord
{
    public static function tableName(){ return '{{%card_service}}'; }
    public $enableAutoPk = false;
    public function rules(){ return [[['card_id','service_id'],'required'], [['card_id','service_id'],'integer']]; }
}
