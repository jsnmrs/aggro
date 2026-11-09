<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLogDateIndex extends Migration
{
    public function up()
    {
        $this->forge->addKey('log_date', false, false, 'idx_log_date');
        $this->forge->processIndexes('aggro_log');
    }

    public function down()
    {
        $this->forge->dropKey('aggro_log', 'idx_log_date');
    }
}
