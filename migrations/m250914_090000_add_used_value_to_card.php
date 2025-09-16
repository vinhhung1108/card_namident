<?php

use yii\db\Migration;

class m250914_090000_add_used_value_to_card extends Migration
{
    public function safeUp()
    {
        // Thêm cột đã sử dụng
        $this->addColumn('{{%card}}', 'used_value',
            $this->bigInteger()->unsigned()->notNull()->defaultValue(0)->after('value')
        );

        // Backfill: nếu trước đây remaining_value đã có -> suy ra used_value
        $this->execute('UPDATE {{%card}} SET used_value = GREATEST(value - IFNULL(remaining_value,0), 0)');

        // Đảm bảo remaining_value nhất quán
        $this->execute('UPDATE {{%card}} SET remaining_value = GREATEST(value - used_value, 0)');

        // (tuỳ chọn) index để lọc nhanh
        $this->createIndex('idx_card_used_value', '{{%card}}', 'used_value');
    }

    public function safeDown()
    {
        $this->dropIndex('idx_card_used_value', '{{%card}}');
        $this->dropColumn('{{%card}}', 'used_value');
    }
}
