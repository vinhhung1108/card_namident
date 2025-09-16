<?php
namespace app\commands;
use yii\console\Controller;
use app\models\User;

class UserController extends Controller {
  public function actionCreate($username, $password, $email = '')
{
    $u = new \app\models\User();
    $u->username = $username;
    $u->email    = $email;
    $u->setPassword($password);
    $u->status = \app\models\User::STATUS_ACTIVE;

    if ($u->save()) {
        $this->stdout("Created user #{$u->id}\n");
    } else {
        print_r($u->errors);
    }
}
  public function actionAssign($username,$role){
    $u=User::findOne(['username'=>$username]); if(!$u){echo "User not found\n"; return 1;}
    $auth=\Yii::$app->authManager; $r=$auth->getRole($role) ?: $auth->getPermission($role);
    if(!$r){echo "Role/permission not found\n"; return 1;}
    $auth->assign($r,$u->id); echo "Assigned {$role} to {$username}\n";
  }
}
