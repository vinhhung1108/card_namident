<?php

use yii\db\Migration;

class m250914_150000_create_card_usage_tables extends Migration
{
    public function safeUp()
    {
        // card_usage
        $this->createTable('{{%card_usage}}', [
            'id'         => $this->bigPrimaryKey()->unsigned(),
            'card_id'    => $this->bigInteger()->unsigned()->notNull(),
            'amount'     => $this->bigInteger()->unsigned()->notNull(),
            'partner_id' => $this->bigInteger()->unsigned()->null(),
            'note'       => $this->text()->null(),
            'used_at'    => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_at' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'updated_at' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('idx_card_usage_card', '{{%card_usage}}', 'card_id');
        $this->createIndex('idx_card_usage_partner', '{{%card_usage}}', 'partner_id');
        $this->createIndex('idx_card_usage_used_at', '{{%card_usage}}', 'used_at');

        $this->addForeignKey('fk_card_usage_card', '{{%card_usage}}', 'card_id', '{{%card}}', 'id', 'CASCADE', 'RESTRICT');
        $this->addForeignKey('fk_card_usage_partner', '{{%card_usage}}', 'partner_id', '{{%partner}}', 'id', 'SET NULL', 'RESTRICT');
        $this->addForeignKey('fk_card_usage_cby', '{{%card_usage}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'RESTRICT');
        $this->addForeignKey('fk_card_usage_uby', '{{%card_usage}}', 'updated_by', '{{%user}}', 'id', 'SET NULL', 'RESTRICT');

        // N-n: usage - services
        $this->createTable('{{%card_usage_service}}', [
            'usage_id'   => $this->bigInteger()->unsigned()->notNull(),
            'service_id' => $this->bigInteger()->unsigned()->notNull(),
        ]);
        $this->addPrimaryKey('pk_card_usage_service', '{{%card_usage_service}}', ['usage_id', 'service_id']);
        $this->addForeignKey('fk_cus_usage', '{{%card_usage_service}}', 'usage_id', '{{%card_usage}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_cus_service', '{{%card_usage_service}}', 'service_id', '{{%service}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_cus_service', '{{%card_usage_service}}');
        $this->dropForeignKey('fk_cus_usage', '{{%card_usage_service}}');
        $this->dropTable('{{%card_usage_service}}');

        $this->dropForeignKey('fk_card_usage_uby', '{{%card_usage}}');
        $this->dropForeignKey('fk_card_usage_cby', '{{%card_usage}}');
        $this->dropForeignKey('fk_card_usage_partner', '{{%card_usage}}');
        $this->dropForeignKey('fk_card_usage_card', '{{%card_usage}}');
        $this->dropTable('{{%card_usage}}');
    }
}
