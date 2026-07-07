<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlaysRefreshColumns extends Migration
{
    public function up()
    {
        $this->forge->addColumn('aggro_videos', [
            'plays_date_updated' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'plays_issue_count' => [
                'type'    => 'INT',
                'null'    => false,
                'default' => 0,
            ],
        ]);

        $this->forge->addKey(['flag_bad', 'plays_date_updated'], false, false, 'idx_video_plays_refresh');
        $this->forge->processIndexes('aggro_videos');
    }

    public function down()
    {
        $this->forge->dropKey('aggro_videos', 'idx_video_plays_refresh');
        $this->forge->dropColumn('aggro_videos', 'plays_date_updated');
        $this->forge->dropColumn('aggro_videos', 'plays_issue_count');
    }
}
