<?php
use yii\db\Migration;

class m250912_053602_m250912_000000_init_cards extends Migration
{
    public function up()
    {
        $opt = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        $this->createTable('{{%service}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->text(),
        ], $opt);

        $this->createTable('{{%partner}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'name' => $this->string(255)->notNull(),
            'address' => $this->string(255),
            'phone' => $this->string(50),
            'email' => $this->string(150),
            'note' => $this->text(),
        ], $opt);

        $this->createTable('{{%referral}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'code' => $this->string(50)->notNull()->unique(),
            'description' => $this->text(),
        ], $opt);

        $this->createTable('{{%card}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'card_code' => $this->string(50)->notNull()->unique(),
            'value' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'remaining_value' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'expired_at' => $this->date()->null(),
            'referral_id' => $this->bigInteger()->unsigned()->null(),
            'created_at' => $this->integer()->unsigned()->notNull()->defaultValue(time()),
            'updated_at' => $this->integer()->unsigned()->notNull()->defaultValue(time()),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $opt);

        $this->createIndex('idx_card_expired_at', '{{%card}}', 'expired_at');
        $this->addForeignKey('fk_card_referral', '{{%card}}', 'referral_id', '{{%referral}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%card_service}}', [
            'card_id' => $this->bigInteger()->unsigned()->notNull(),
            'service_id' => $this->bigInteger()->unsigned()->notNull(),
        ], $opt);
        $this->addPrimaryKey('pk_card_service', '{{%card_service}}', ['card_id','service_id']);
        $this->addForeignKey('fk_card_service_card', '{{%card_service}}', 'card_id', '{{%card}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_card_service_service', '{{%card_service}}', 'service_id', '{{%service}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%card_partner}}', [
            'card_id' => $this->bigInteger()->unsigned()->notNull(),
            'partner_id' => $this->bigInteger()->unsigned()->notNull(),
        ], $opt);
        $this->addPrimaryKey('pk_card_partner', '{{%card_partner}}', ['card_id','partner_id']);
        $this->addForeignKey('fk_card_partner_card', '{{%card_partner}}', 'card_id', '{{%card}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_card_partner_partner', '{{%card_partner}}', 'partner_id', '{{%partner}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%card_partner}}');
        $this->dropTable('{{%card_service}}');
        $this->dropForeignKey('fk_card_referral', '{{%card}}');
        $this->dropTable('{{%card}}');
        $this->dropTable('{{%referral}}');
        $this->dropTable('{{%partner}}');
        $this->dropTable('{{%service}}');
    }
}
