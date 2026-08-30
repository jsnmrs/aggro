<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDurationIssueCount extends Migration
{
    public function up()
    {
        $this->forge->addColumn('aggro_videos', [
            'duration_issue_count' => [
                'type'    => 'INT',
                'null'    => false,
                'default' => 0,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('aggro_videos', 'duration_issue_count');
    }
}
