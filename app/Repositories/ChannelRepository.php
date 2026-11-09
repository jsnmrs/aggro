<?php

namespace App\Repositories;

use App\Models\UtilityModels;
use Config\Database;

/**
 * Repository for channel/source-related database operations.
 */
class ChannelRepository
{
    /**
     * Multiplier applied to the stale window per consecutive failure count.
     * Index = source_fail_count, value = multiplier on $stale minutes.
     * The final entry applies to all fail counts at or above its index,
     * up to the slow retry threshold.
     * Example with $stale = 30: 0 fails -> 30min, 1 -> 1h, 2 -> 2h, 3 -> 4h,
     * 4 -> 8h, 5+ -> 24h.
     */
    private const FAIL_BACKOFF_MULTIPLIERS = [1, 2, 4, 8, 16, 48];

    /**
     * Channels with at least this many consecutive failures move to the
     * slow retry schedule until a successful fetch resets source_fail_count.
     */
    private const FAIL_COUNT_SLOW_RETRY = 20;

    /**
     * Multiplier applied to the stale window for channels on the slow
     * retry schedule. Example with $stale = 30: one attempt every 7 days.
     */
    private const SLOW_RETRY_MULTIPLIER = 336;

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
        $now     = time();
        $cutoffs = [];

        foreach (self::FAIL_BACKOFF_MULTIPLIERS as $bucket => $multiplier) {
            $cutoffs[$bucket] = date('Y-m-d H:i:s', $now - ((int) $stale * 60 * $multiplier));
        }
        $lastBucket      = array_key_last(self::FAIL_BACKOFF_MULTIPLIERS);
        $slowRetryCutoff = date('Y-m-d H:i:s', $now - ((int) $stale * 60 * self::SLOW_RETRY_MULTIPLIER));

        // Channels below the slow retry threshold follow the backoff
        // buckets. Channels at or above it are stale once a slow retry
        // window has passed.
        $builder = $this->db->table('aggro_sources')
            ->where('source_type', $type)
            ->groupStart()
            ->groupStart()
            ->where('source_fail_count <', self::FAIL_COUNT_SLOW_RETRY)
            ->groupStart();

        foreach (array_keys(self::FAIL_BACKOFF_MULTIPLIERS) as $bucket) {
            $bucket === 0 ? $builder->groupStart() : $builder->orGroupStart();

            $failCountColumn = $bucket === $lastBucket ? 'source_fail_count >=' : 'source_fail_count';
            $builder->where($failCountColumn, $bucket);

            $builder->where('source_date_updated <=', $cutoffs[$bucket])->groupEnd();
        }

        $query = $builder->groupEnd()
            ->groupEnd()
            ->orGroupStart()
            ->where('source_fail_count >=', self::FAIL_COUNT_SLOW_RETRY)
            ->where('source_date_updated <=', $slowRetryCutoff)
            ->groupEnd()
            ->groupEnd()
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

    /**
     * Increment consecutive failure count for a channel.
     *
     * Logs an error once, as the count reaches the slow retry threshold,
     * so a channel that has stopped working does not go quiet unnoticed.
     *
     * @param string $sourceSlug Source slug.
     *
     * @return void
     */
    public function incrementChannelFailCount($sourceSlug)
    {
        $this->db->table('aggro_sources')
            ->where('source_slug', $sourceSlug)
            ->set('source_fail_count', 'source_fail_count + 1', false)
            ->update();

        $channel = $this->db->table('aggro_sources')
            ->select('source_fail_count')
            ->where('source_slug', $sourceSlug)
            ->get()
            ->getRow();

        if ($channel !== null && (int) $channel->source_fail_count === self::FAIL_COUNT_SLOW_RETRY) {
            log_message('error', 'Channel ' . $sourceSlug . ' failed ' . self::FAIL_COUNT_SLOW_RETRY . ' fetches in a row and moves to the slow retry schedule.');
        }
    }

    /**
     * Reset consecutive failure count for a channel.
     *
     * @param string $sourceSlug Source slug.
     *
     * @return void
     */
    public function resetChannelFailCount($sourceSlug)
    {
        $this->db->table('aggro_sources')
            ->where('source_slug', $sourceSlug)
            ->update(['source_fail_count' => 0]);
    }
}
