<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;
use RuntimeException;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * All interactions with news_* tables.
 */
class NewsModels extends Model
{
    /**
     * Build featured, stream entries.
     *
     * @return bool
     *              Fetched true.
     */
    public function featuredBuilder()
    {
        $utilityModel = new UtilityModels();
        helper(['aggro', 'text']);

        try {
            $featured = $this->getFeaturedFeeds();
            if ($featured === false || empty($featured)) {
                return false;
            }

            $stats = $this->processFeaturedFeeds($featured);

            $this->logFeaturedStats($utilityModel, $stats);

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
        $this->db->transStart();

        $this->updateLastFetchTime($row->site_id);
        $this->processFeedItems($row, $fetch);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            log_message('error', 'Transaction failed for site_id ' . $row->site_id);

            return false;
        }

        return true;
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
            'story_title'     => $item->get_title(),
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
     * @param UtilityModels $utilityModel
     * @param array         $stats
     */
    private function logFeaturedStats($utilityModel, $stats)
    {
        $message = $stats['counter'] . ' featured and stream sites updated';
        if ($stats['errorCount'] > 0) {
            $message .= ', ' . $stats['errorCount'] . ' errors';
        }
        $utilityModel->sendLog($message);
    }

    /**
     * Clean featured table.
     *
     * @return false|int
     *                   Number of removed stories or false on error.
     */
    public function featuredCleaner()
    {
        $utilityModel = new UtilityModels();

        try {
            $this->db->transStart();

            // Single optimized query to delete all old stories at once
            $storageConfig = config('Storage');
            $cutoffDate    = date('Y-m-d H:i:s', strtotime("-{$storageConfig->cleanupDays} days"));
            $this->db->table('news_featured')
                ->where('story_date <', $cutoffDate)
                ->delete();
            $counter = $this->db->affectedRows();

            $this->db->transCommit();

            $message = $counter . ' old stories deleted.';
            $utilityModel->sendLog($message);

            return $counter;
        } catch (DatabaseException $e) {
            $this->db->transRollback();
            log_message('error', 'Database error in featuredCleaner: ' . $e->getMessage());
            $utilityModel->sendLog('Failed to clean old stories: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get featured page.
     *
     * @return array
     *               Featured page.
     */
    public function featuredPage()
    {
        // Single optimized query to fetch feeds and their top 3 stories using window function
        $sql = 'SELECT
                    nf.site_name,
                    nf.site_slug,
                    nf.site_date_last_post,
                    nf.site_id,
                    feat.story_title,
                    feat.story_permalink,
                    feat.story_hash,
                    feat.story_date,
                    ROW_NUMBER() OVER (PARTITION BY nf.site_id ORDER BY feat.story_date DESC) as story_rank
                FROM news_feeds nf
                LEFT JOIN news_featured feat ON nf.site_id = feat.site_id
                WHERE nf.flag_featured = 1
                ORDER BY nf.site_name, feat.story_date DESC';

        $query  = $this->db->query($sql);
        $result = $query->getResult('array');

        // Build the structured array from the joined result
        $built = [];

        foreach ($result as $row) {
            $siteSlug = $row['site_slug'];

            // Initialize site data if not already set
            if (! isset($built[$siteSlug])) {
                $built[$siteSlug] = [
                    'site_name'           => $row['site_name'],
                    'site_slug'           => $row['site_slug'],
                    'site_date_last_post' => $row['site_date_last_post'],
                ];
            }

            // Add stories (limit to top 3 per site using story_rank from window function)
            if ($row['story_title'] && $row['story_rank'] <= 3) {
                $storyNum                    = 'story' . $row['story_rank'];
                $built[$siteSlug][$storyNum] = [
                    'story_title'     => $row['story_title'],
                    'story_permalink' => $row['story_permalink'],
                    'story_hash'      => $row['story_hash'],
                ];
            }
        }

        return $built;
    }

    /**
     * Get single site.
     *
     * @param string $slug
     *                     Site slug.
     *
     * @return array
     *               Site data from table.
     */
    public function getSite($slug)
    {
        $query = $this->db->table('news_feeds')
            ->where('site_slug', $slug)
            ->limit(1)
            ->get();

        return $query->getRowArray();
    }

    /**
     * Get all sites.
     *
     * @return array
     *               Site data from table.
     */
    public function getSites()
    {
        $query = $this->db->table('news_feeds')
            ->orderBy('site_name', 'ASC')
            ->get();

        if ($query === false) {
            return [];
        }

        return $query->getResult();
    }

    /**
     * Get recent directory updates.
     *
     * @return array
     *               All feed data from table.
     */
    public function getSitesRecent()
    {
        $query = $this->db->table('news_feeds')
            ->orderBy('site_date_added', 'DESC')
            ->limit(10)
            ->get();

        if ($query === false) {
            return [];
        }

        return $query->getResult();
    }

    /**
     * Build stream page.
     *
     * @param int $page  Page number (default: 1)
     * @param int $limit Stories per page (default: 50)
     *
     * @return array
     *               Stream page.
     */
    public function streamPage($page = 1, $limit = 50)
    {
        // Ensure numeric values
        $page  = (int) $page;
        $limit = (int) $limit;

        // Ensure reasonable values
        $page  = max($page, 1);  // Minimum page 1
        $limit = max($limit, 1); // Minimum 1 item
        $limit = min($limit, 100); // Max 100 items per page

        // Calculate offset for pagination
        $offset = ($page - 1) * $limit;
        $offset = max($offset, 0);  // No negative offset

        $query = $this->db->table('news_featured')
            ->select([
                'news_feeds.site_name',
                'news_feeds.site_slug',
                'news_featured.story_title',
                'news_featured.story_permalink',
                'news_featured.story_date',
                'news_featured.story_hash',
            ])
            ->join('news_feeds', 'news_featured.site_id = news_feeds.site_id', 'inner')
            ->orderBy('news_featured.story_date', 'DESC')
            ->limit((int) $limit, (int) $offset)
            ->get();

        return $query->getResult();
    }

    /**
     * Get total count of featured stories for pagination.
     *
     * @return int Total number of featured stories
     */
    public function getStreamPageTotal()
    {
        $query = $this->db->table('news_featured')
            ->selectCount('news_featured.site_id', 'total')
            ->join('news_feeds', 'news_featured.site_id = news_feeds.site_id', 'inner')
            ->get();
        $result = $query->getRow();

        return (int) $result->total;
    }

    /**
     * Update feed data.
     *
     * @param string $slug
     *                     Site slug (as ID).
     * @param object $feed
     *                     Fetched feed object.
     *
     * @return void
     */
    public function updateFeed($slug, $feed)
    {
        foreach ($feed->get_items(0, 1) as $item) {
            $lastPost = $item->get_date('Y-m-d H:i:s');
        }

        if (isset($lastPost)) {
            $lastFetch = date('Y-m-d H:i:s');

            $this->db->table('news_feeds')
                ->where('site_slug', $slug)
                ->update([
                    'site_date_last_fetch' => $lastFetch,
                    'site_date_last_post'  => $lastPost,
                ]);
        }
    }
}
