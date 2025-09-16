<?php

use yii\db\Migration;

class m250914_170000_create_partner_service_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%partner_service}}', [
            // PHẢI khớp với kiểu cột id của bảng gốc (BIGINT UNSIGNED)
            'partner_id' => $this->bigInteger()->unsigned()->notNull(),
            'service_id' => $this->bigInteger()->unsigned()->notNull(),
        ]);

        $this->addPrimaryKey(
            'pk_partner_service',
            '{{%partner_service}}',
            ['partner_id','service_id']
        );

        $this->createIndex('idx_ps_partner', '{{%partner_service}}', 'partner_id');
        $this->createIndex('idx_ps_service', '{{%partner_service}}', 'service_id');

        $this->addForeignKey(
            'fk_ps_partner',
            '{{%partner_service}}', 'partner_id',
            '{{%partner}}', 'id',
            'CASCADE', 'RESTRICT'
        );
        $this->addForeignKey(
            'fk_ps_service',
            '{{%partner_service}}', 'service_id',
            '{{%service}}', 'id',
            'CASCADE', 'RESTRICT'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_ps_service', '{{%partner_service}}');
        $this->dropForeignKey('fk_ps_partner', '{{%partner_service}}');
        $this->dropPrimaryKey('pk_partner_service', '{{%partner_service}}');
        $this->dropTable('{{%partner_service}}');
    }
}
