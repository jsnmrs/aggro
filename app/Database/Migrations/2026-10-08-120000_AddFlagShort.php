<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFlagShort extends Migration
{
    public function up()
    {
        $this->forge->addColumn('aggro_videos', [
            'flag_short' => [
                'type'    => 'INT',
                'null'    => false,
                'default' => 0,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('aggro_videos', 'flag_short');
    }
}
