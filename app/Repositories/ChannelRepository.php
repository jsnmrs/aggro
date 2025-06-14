<?php

namespace App\Repositories;

use App\Models\UtilityModels;
use Config\Database;

/**
 * Repository for channel/source-related database operations.
 */
class ChannelRepository
{
    protected $db;
    protected $utilityModel;

    public function __construct()
    {
        $this->db           = Database::connect();
        $this->utilityModel = new UtilityModels();
    }

    /**
     * Get list of video channels that haven't been updated within timeframe.
     *
     * @param string $stale
     *                      Max age in minutes.
     * @param string $type
     *                      youtube, vimeo.
     * @param string $limit
     *                      Limit results returned.
     *
     * @return array|false
     *                     Channel data from table or FALSE.
     */
    public function getChannels($stale = '30', $type = 'youtube', $limit = '10')
    {
        $channels = $this->fetchStaleChannels($stale, $type, $limit);
        $this->logChannelSearchResult($channels, $type, $limit);

        return $channels;
    }

    /**
     * Fetch stale channels from database.
     *
     * @param string $stale
     * @param string $type
     * @param string $limit
     *
     * @return array|false
     */
    private function fetchStaleChannels($stale, $type, $limit)
    {
        $staleDateTime = date('Y-m-d H:i:s', strtotime("-{$stale} minutes"));

        $query = $this->db->table('aggro_sources')
            ->where('source_type', $type)
            ->where('source_date_updated <=', $staleDateTime)
            ->orderBy('source_date_updated', 'ASC')
            ->limit((int) $limit)
            ->get();

        $results = $query->getResultArray();

        return count($results) > 0 ? $query->getResult() : false;
    }

    /**
     * Log channel search results.
     *
     * @param array|false $channels
     * @param string      $type
     * @param string      $limit
     */
    private function logChannelSearchResult($channels, $type, $limit)
    {
        $count        = $channels === false ? 0 : count($channels);
        $updateStatus = $count > 0 ? ' Updating...' : '';
        $message      = "Looking for {$limit} {$type} channels. Found {$count} stale {$type} channels.{$updateStatus}";

        $this->utilityModel->sendLog($message);
    }

    /**
     * Update video source last fetch timestamp.
     *
     * @param string $sourceSlug
     *                           Source slug.
     *
     * @return void
     */
    public function updateChannel($sourceSlug)
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('aggro_sources')
            ->where('source_slug', $sourceSlug)
            ->update(['source_date_updated' => $now]);
    }
}
