<?php
namespace app\models;
use yii\db\ActiveRecord;

class CardPartner extends ActiveRecord
{
    public static function tableName(){ return '{{%card_partner}}'; }
    public $enableAutoPk = false;
    public function rules(){ return [[['card_id','partner_id'],'required'], [['card_id','partner_id'],'integer']]; }
}
