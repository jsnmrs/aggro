<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropDurationColumns extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('aggro_videos', ['video_duration', 'duration_issue_count']);
    }

    public function down()
    {
        $this->forge->addColumn('aggro_videos', [
            'video_duration' => [
                'type'    => 'INT',
                'null'    => false,
                'default' => 0,
            ],
            'duration_issue_count' => [
                'type'    => 'INT',
                'null'    => false,
                'default' => 0,
            ],
        ]);
    }
}
