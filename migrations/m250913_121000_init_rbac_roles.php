<?php
use yii\db\Migration;

class m250913_121000_init_rbac_roles extends Migration
{
    public function safeUp()
    {
        $auth = \Yii::$app->authManager;

        // permissions
        $manageCard     = $auth->createPermission('manageCard');     $manageCard->description='Quản lý thẻ';       $auth->add($manageCard);
        $manageService  = $auth->createPermission('manageService');  $manageService->description='Quản lý dịch vụ'; $auth->add($manageService);
        $managePartner  = $auth->createPermission('managePartner');  $managePartner->description='Quản lý đối tác'; $auth->add($managePartner);
        $manageReferral = $auth->createPermission('manageReferral'); $manageReferral->description='Quản lý mã GT';  $auth->add($manageReferral);

        // roles
        $staff   = $auth->createRole('staff');   $auth->add($staff);
        $manager = $auth->createRole('manager'); $auth->add($manager);
        $admin   = $auth->createRole('admin');   $auth->add($admin);

        // role -> permissions
        $auth->addChild($staff, $manageCard);

        $auth->addChild($manager, $staff);
        $auth->addChild($manager, $manageService);
        $auth->addChild($manager, $managePartner);
        $auth->addChild($manager, $manageReferral);

        $auth->addChild($admin, $manager);

        // gán admin cho user id=1 (đổi id nếu cần)
        $auth->assign($admin, 1);
    }

    public function safeDown()
    {
        \Yii::$app->authManager->removeAll();
    }
}
