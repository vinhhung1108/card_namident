<?php

use yii\db\Migration;

class m250913_120000_create_user_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user}}', [
            'id'            => $this->primaryKey(),
            'username'      => $this->string(50)->notNull()->unique(),
            'password_hash' => $this->string()->notNull(),
            'auth_key'      => $this->string(32)->notNull(),
            'email'         => $this->string()->unique(),
            'full_name'     => $this->string(100),
            'status'        => $this->smallInteger()->notNull()->defaultValue(10), // 10=active
            'created_at'    => $this->integer()->notNull(),
            'updated_at'    => $this->integer()->notNull(),
        ]);

        // seed admin
        $this->insert('{{%user}}', [
            'username'      => 'admin',
            'password_hash' => Yii::$app->security->generatePasswordHash('Admin@123'), // đổi ngay sau khi login
            'auth_key'      => Yii::$app->security->generateRandomString(),
            'email'         => 'admin@namident.com',
            'full_name'     => 'Administrator',
            'status'        => 10,
            'created_at'    => time(),
            'updated_at'    => time(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
