<?php

namespace App\Services;

use App\Models\UtilityModels;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Config\Database;
use RuntimeException;

/**
 * Service for ingesting featured and stream feeds into the database.
 */
class FeedIngestionService
{
    protected $db;
    protected $utilityModel;

    public function __construct(?UtilityModels $utilityModel = null)
    {
        $this->db           = Database::connect();
        $this->utilityModel = $utilityModel ?? new UtilityModels();
    }

    /**
     * Build featured, stream entries.
     *
     * @return bool
     *              Fetched true.
     */
    public function featuredBuilder()
    {
        helper(['aggro', 'text']);

        try {
            $featured = $this->getFeaturedFeeds();
            if ($featured === false || empty($featured)) {
                return false;
            }

            $stats = $this->processFeaturedFeeds($featured);

            $this->logFeaturedStats($stats);

            return true;
        } catch (DatabaseException $e) {
            log_message('error', 'Database error in featuredBuilder: ' . $e->getMessage());

            return false;
        } catch (RuntimeException $e) {
            log_message('error', 'Runtime error in featuredBuilder: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get featured and stream feeds from database.
     *
     * @return array|false
     */
    private function getFeaturedFeeds()
    {
        $query = $this->db->table('news_feeds')
            ->groupStart()
            ->where('flag_featured', 1)
            ->orWhere('flag_stream', 1)
            ->groupEnd()
            ->orderBy('site_name', 'ASC')
            ->get();

        if ($query === false) {
            log_message('error', 'Failed to query featured feeds');

            return false;
        }

        return $query->getResult();
    }

    /**
     * Process all featured feeds.
     *
     * @param array $featured
     *
     * @return array Stats with counter and errorCount
     */
    private function processFeaturedFeeds($featured)
    {
        $counter    = 0;
        $errorCount = 0;

        foreach ($featured as $row) {
            $result = $this->processSingleFeed($row);
            if ($result === true) {
                $counter++;
            }
            if ($result !== true) {
                $errorCount++;
            }
        }

        return ['counter' => $counter, 'errorCount' => $errorCount];
    }

    /**
     * Process a single feed.
     *
     * @param object $row
     *
     * @return bool Success or failure
     */
    private function processSingleFeed($row)
    {
        try {
            $fetch = fetch_feed($row->site_feed, $row->flag_spoof);

            if ($fetch === false || $fetch->error()) {
                log_message('warning', 'Failed to fetch feed for site_id ' . $row->site_id . ': ' . $row->site_feed);

                return false;
            }

            return $this->saveFeedItems($row, $fetch);
        } catch (RuntimeException $e) {
            log_message('error', 'Runtime error processing site_id ' . $row->site_id . ': ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Save feed items to database.
     *
     * @param object $row
     * @param object $fetch
     *
     * @return bool Success or failure
     */
    private function saveFeedItems($row, $fetch)
    {
        $maxAttempts = 3;
        $delaysMs    = [50, 150, 400];

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $this->db->transStart();

            $this->updateLastFetchTime($row->site_id);
            $this->processFeedItems($row, $fetch);

            $this->db->transComplete();

            if ($this->db->transStatus() !== false) {
                return true;
            }

            if ($this->isDeadlockError() && $attempt < $maxAttempts) {
                $base   = $delaysMs[$attempt - 1];
                $jitter = random_int(0, $base);
                $this->sleepBetweenAttempts($base + $jitter);

                continue;
            }

            log_message(
                'error',
                'Transaction failed for site_id ' . $row->site_id
                . ' (attempt ' . $attempt . '/' . $maxAttempts . ')',
            );

            return false;
        }

        return false;
    }

    /**
     * Detect a transient MySQL deadlock or lock-wait timeout from the last query.
     */
    private function isDeadlockError(): bool
    {
        $error = $this->db->error();
        $code  = (int) ($error['code'] ?? 0);

        return $code === 1213
            || $code === 1205
            || ($error['sqlstate'] ?? '') === '40001';
    }

    /**
     * Sleep between retry attempts. Extracted so tests can override it.
     */
    protected function sleepBetweenAttempts(int $ms): void
    {
        usleep($ms * 1000);
    }

    /**
     * Update last fetch time for feed.
     *
     * @param int $siteId
     */
    private function updateLastFetchTime($siteId)
    {
        $this->db->table('news_feeds')
            ->where('site_id', $siteId)
            ->update(['site_date_last_fetch' => date('Y-m-d H:i:s')]);
    }

    /**
     * Process individual items from feed.
     *
     * @param object $row
     * @param object $fetch
     */
    private function processFeedItems($row, $fetch)
    {
        $storyCount = 0;

        foreach ($fetch->get_items(0, 10) as $item) {
            try {
                if ($storyCount === 0) {
                    $this->updateLastPostTime($row->site_id, $item);
                }

                $this->insertFeedItem($row->site_id, $item);
                $storyCount++;
            } catch (DatabaseException $e) {
                log_message('error', 'Database error processing item for site_id ' . $row->site_id . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Update last post time for feed.
     *
     * @param int    $siteId
     * @param object $item
     */
    private function updateLastPostTime($siteId, $item)
    {
        $lastPost = $item->get_date('Y-m-d H:i:s');
        $this->db->table('news_feeds')
            ->where('site_id', $siteId)
            ->update(['site_date_last_post' => $lastPost]);
    }

    /**
     * Insert feed item into database.
     *
     * @param int    $siteId
     * @param object $item
     */
    private function insertFeedItem($siteId, $item)
    {
        $data = [
            'site_id'         => $siteId,
            'story_title'     => decode_entities($item->get_title()),
            'story_permalink' => $item->get_permalink(),
            'story_hash'      => sha1($item->get_permalink()),
            'story_date'      => $item->get_date('Y-m-d H:i:s'),
        ];

        // Use replace to simulate INSERT IGNORE behavior
        $this->db->table('news_featured')->replace($data);
    }

    /**
     * Log featured feed processing statistics.
     *
     * @param array $stats
     */
    private function logFeaturedStats($stats)
    {
        $message = $stats['counter'] . ' featured and stream sites updated';
        if ($stats['errorCount'] > 0) {
            $message .= ', ' . $stats['errorCount'] . ' errors';
        }
        $this->utilityModel->sendLog($message);
    }
}
