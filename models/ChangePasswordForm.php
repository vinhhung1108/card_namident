<?php
namespace app\models;
use Yii;


class ChangePasswordForm extends \yii\base\Model
{
    public $current_password;
    public $new_password;
    public $repeat_password;

    public function rules()
    {
        return [
            [['current_password','new_password','repeat_password'],'required'],
            ['repeat_password','compare','compareAttribute'=>'new_password'],
            ['new_password','string','min'=>8],
            ['current_password', function($attr){
                if (!Yii::$app->user->identity->validatePassword($this->current_password)) {
                    $this->addError($attr, 'Mật khẩu hiện tại không đúng.');
                }
            }],
        ];
    }

    public function change()
    {
        $u = Yii::$app->user->identity;
        $u->password = $this->new_password; // dùng logic trong User::beforeSave
        return $u->save(false);
    }
}
