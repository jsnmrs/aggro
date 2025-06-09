<?php

namespace Tests\Support\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTestTables extends Migration
{
    public function up()
    {
        // Create aggro_videos table
        $this->forge->addField([
            'aggro_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'aggro_date_added' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'aggro_date_updated' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'video_id' => [
                'type' => 'VARCHAR',
                'constraint' => 25,
                'null' => false,
                'default' => '',
            ],
            'video_plays' => [
                'type' => 'INT',
                'constraint' => 25,
                'null' => false,
                'default' => 0,
            ],
            'video_title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'video_thumbnail_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'video_date_uploaded' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'video_width' => [
                'type' => 'INT',
                'constraint' => 6,
                'null' => false,
                'default' => 0,
            ],
            'video_height' => [
                'type' => 'INT',
                'constraint' => 6,
                'null' => false,
                'default' => 0,
            ],
            'video_aspect_ratio' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => false,
                'default' => '16:9',
            ],
            'video_duration' => [
                'type' => 'INT',
                'constraint' => 10,
                'null' => false,
                'default' => 0,
            ],
            'video_type' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'video_source_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'video_source_username' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'video_source_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'flag_archive' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
            ],
            'flag_bad' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
            ],
        ]);
        $this->forge->addKey('aggro_id', true);
        $this->forge->addUniqueKey('video_id');
        $this->forge->createTable('aggro_videos');

        // Create aggro_sources table
        $this->forge->addField([
            'source_id' => [
                'type' => 'INT',
                'constraint' => 6,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'source_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
                'default' => '',
            ],
            'source_slug' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
                'default' => '',
            ],
            'source_type' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
                'default' => '',
            ],
            'source_date_updated' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        $this->forge->addKey('source_id', true);
        $this->forge->createTable('aggro_sources');

        // Create news_feeds table
        $this->forge->addField([
            'site_id' => [
                'type' => 'SMALLINT',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'site_name' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'default' => '',
            ],
            'site_feed' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
                'default' => '',
            ],
            'flag_featured' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'unsigned' => true,
                'null' => false,
                'default' => 0,
            ],
            'flag_stream' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
            ],
            'flag_spoof' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
            ],
            'site_date_added' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'site_date_last_fetch' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        $this->forge->addKey('site_id', true);
        $this->forge->createTable('news_feeds');
    }

    public function down()
    {
        $this->forge->dropTable('aggro_videos');
        $this->forge->dropTable('aggro_sources');
        $this->forge->dropTable('news_feeds');
    }
}