<?php

use yii\db\Migration;

class m250915_000000_add_issue_price_to_card extends Migration
{
    public function safeUp()
    {
        // bigInteger UNSIGNED NOT NULL DEFAULT 0 (giá trị tiền lớn)
        $this->addColumn('{{%card}}', 'issue_price',
            $this->bigInteger()->unsigned()->notNull()->defaultValue(0)->after('value')
        );
    }

    public function safeDown()
    {
        $this->dropColumn('{{%card}}', 'issue_price');
    }
}
