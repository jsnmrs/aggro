<?php

namespace App\Models;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

/**
 * All interactions with news_* tables.
 */
class NewsModels extends Model
{
    protected $utilityModel;

    public function __construct(?UtilityModels $utilityModel = null)
    {
        parent::__construct();
        $this->utilityModel = $utilityModel ?? new UtilityModels();
    }

    /**
     * Clean featured table.
     *
     * @return false|int
     *                   Number of removed stories or false on error.
     */
    public function featuredCleaner()
    {
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
            $this->utilityModel->sendLog($message);

            return $counter;
        } catch (DatabaseException $e) {
            $this->db->transRollback();
            log_message('error', 'Database error in featuredCleaner: ' . $e->getMessage());
            $this->utilityModel->sendLog('Failed to clean old stories: ' . $e->getMessage());

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
        $page  = max($page, 1);      // Minimum page 1
        $page  = min($page, 100000); // Maximum 100000 pages to prevent overflow
        $limit = max($limit, 1);     // Minimum 1 item
        $limit = min($limit, 100);   // Max 100 items per page

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
