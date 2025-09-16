<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public $password;        // mật khẩu nhập mới/đổi
    public $password_repeat; // xác nhận

    const STATUS_ACTIVE = 10;

    public static function tableName(){ return '{{%user}}'; }

    public function rules()
    {
        return [
            // bắt buộc tối thiểu
            [['username'], 'required'],

            // kiểu dữ liệu
            [['status','created_at','updated_at'], 'integer'],
            [['username'], 'string', 'max'=>50],
            [['email','full_name'], 'string', 'max'=>100],
            ['email', 'email'],
            [['username','email'], 'unique'],

            // auth_key: KHÔNG required; tự sinh
            ['auth_key', 'string', 'max'=>32],
            ['auth_key', 'default', 'value' => function(){ return Yii::$app->security->generateRandomString(); }],

            // mật khẩu: required khi tạo mới (scenario 'create'), đổi thì để trống nếu không đổi
            [['password','password_repeat'], 'required', 'on' => 'create'],
            ['password', 'string', 'min' => 8],
            ['password_repeat', 'compare', 'compareAttribute' => 'password'],
        ];
    }

    // ===== IdentityInterface =====
    public static function findIdentity($id){ return static::findOne(['id'=>$id,'status'=>self::STATUS_ACTIVE]); }
    public static function findIdentityByAccessToken($token, $type = null){ return null; }
    public static function findByUsername($username){ return static::findOne(['username'=>$username,'status'=>self::STATUS_ACTIVE]); }
    public function getId(){ return $this->id; }
    public function getAuthKey(){ return $this->auth_key; }
    public function validateAuthKey($authKey){ return $this->auth_key === $authKey; }

    // ===== Password helpers =====
    public function setPassword($password){ $this->password_hash = Yii::$app->security->generatePasswordHash($password); }
    public function validatePassword($password){ return Yii::$app->security->validatePassword($password, $this->password_hash); }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) return false;
        if ($this->isNewRecord && empty($this->auth_key)) {
            $this->generateAuthKey();
        }
        return true;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) return false;

        // nếu người dùng nhập mật khẩu mới -> băm và lưu vào password_hash
        if (!empty($this->password)) {
            $this->setPassword($this->password);
        }

        $this->updated_at = time();
        if ($insert) $this->created_at = $this->updated_at;

        return true;
    }
}
