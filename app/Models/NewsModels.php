<?php

namespace App\Models;

use CodeIgniter\Model;

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

        $sql      = 'SELECT * FROM news_feeds WHERE flag_featured = 1 OR flag_stream = 1 ORDER BY site_name';
        $query    = $this->db->query($sql);
        $featured = $query->getResult();
        $counter  = 0;

        foreach ($featured as $row) {
            $fetch = fetch_feed($row->site_feed, $row->flag_spoof);
            $sql   = "UPDATE news_feeds SET site_date_last_fetch='" . date('Y-m-d H:i:s') . "' WHERE site_id='" . $row->site_id . "'";
            $this->db->query($sql);
            $storyCount = 0;

            foreach ($fetch->get_items(0, 10) as $item) {
                if ($storyCount === 0) {
                    $lastPost = $item->get_date('Y-m-d H:i:s');
                    $sql      = "UPDATE news_feeds SET site_date_last_post='" . $lastPost . "' WHERE site_id='" . $row->site_id . "'";
                    $this->db->query($sql);
                }

                $sql = "INSERT IGNORE INTO news_featured (site_id, story_title, story_permalink, story_hash, story_date) VALUES ('" . $row->site_id . "', '" . quotes_to_entities($item->get_title()) . "', '" . quotes_to_entities($item->get_permalink()) . "', '" . sha1($item->get_permalink()) . "', '" . quotes_to_entities($item->get_date('Y-m-d H:i:s')) . "')";
                $this->db->query($sql);
                $storyCount++;
            }

            $counter++;
        }

        $message = $counter . ' featured and stream sites updated.';
        $utilityModel->sendLog($message);

        return true;
    }

    /**
     * Clean featured table.
     *
     * @return string
     *                Number of removed stories.
     */
    public function featuredCleaner()
    {
        $utilityModel = new UtilityModels();
        $now          = date('Y-m-d H:i:s');
        $counter      = 0;
        $sql          = 'SELECT DISTINCT site_id FROM news_featured';
        $query        = $this->db->query($sql);
        $featured     = $query->getResult();

        foreach ($featured as $row) {
            $innersql = 'SELECT *
                    FROM news_featured
                    WHERE site_id=' . $row->site_id . "
                    AND story_date < DATE_SUB('" . $now . "',INTERVAL 45 DAY)";
            $innerquery   = $this->db->query($innersql);
            $sitefeatured = $innerquery->getResult();

            foreach ($sitefeatured as $innerrow) {
                $cleansql = "DELETE FROM news_featured WHERE story_id='" . $innerrow->story_id . "'";
                $this->db->query($cleansql);
                $counter++;
            }
        }
        $cleanup = 'OPTIMIZE TABLE news_featured';
        $this->db->query($cleanup);
        $message = $counter . ' old stories deleted.';
        $utilityModel->sendLog($message);

        return $counter;
    }

    /**
     * Get featured page.
     *
     * @return array
     *               Featured page.
     */
    public function featuredPage()
    {
        // Fetch all featured news feeds in one query
        $sql       = 'SELECT * FROM news_feeds WHERE flag_featured = 1 ORDER BY site_name';
        $query     = $this->db->query($sql);
        $newsFeeds = $query->getResult('array');

        // Initialize an array to hold the built structure
        $built = [];

        // Loop through each news feed
        foreach ($newsFeeds as $row) {
            // Initialize the site's data
            $built[$row['site_slug']] = [
                'site_name'           => $row['site_name'],
                'site_slug'           => $row['site_slug'],
                'site_date_last_post' => $row['site_date_last_post'],
            ];

            // Fetch the top 3 featured stories for this site in one query
            $innerSql   = 'SELECT * FROM news_featured WHERE site_id = ? ORDER BY story_date DESC LIMIT 3';
            $innerQuery = $this->db->query($innerSql, [$row['site_id']]);
            $stories    = $innerQuery->getResult('array');

            // Add the stories to the built structure
            foreach ($stories as $index => $story) {
                $storyNum                            = 'story' . ($index + 1);
                $built[$row['site_slug']][$storyNum] = [
                    'story_title'     => $story['story_title'],
                    'story_permalink' => $story['story_permalink'],
                    'story_hash'      => $story['story_hash'],
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
        $slug  = esc($slug);
        $sql   = "SELECT * FROM news_feeds WHERE site_slug = '{$slug}' LIMIT 1";
        $query = $this->db->query($sql);

        return $query->getRowArray();
    }

    /**
     * Get all sites.
     *
     * @return object
     *                Site data from table.
     */
    public function getSites()
    {
        $sql   = 'SELECT * FROM news_feeds ORDER BY site_name';
        $query = $this->db->query($sql);

        return $query->getResult();
    }

    /**
     * Get recent directory updates.
     *
     * @return object
     *                All feed data from table.
     */
    public function getSitesRecent()
    {
        $sql   = 'SELECT * FROM news_feeds ORDER BY site_date_added DESC LIMIT 10';
        $query = $this->db->query($sql);

        return $query->getResult();
    }

    /**
     * Build stream page.
     *
     * @return array
     *               Stream page.
     */
    public function streamPage()
    {
        $sql = 'SELECT news_feeds.site_name, news_feeds.site_slug, news_featured.story_title, news_featured.story_permalink, news_featured.story_date, news_featured.story_hash
            FROM news_featured
            INNER JOIN news_feeds
            ON news_featured.site_id = news_feeds.site_id
            ORDER BY news_featured.story_date DESC
            LIMIT 300';
        $query = $this->db->query($sql);

        return $query->getResult();
    }

    /**
     * Update feed data.
     *
     * @param string $slug
     *                     Site slug (as ID).
     * @param object $feed
     *                     Fetched feed object.
     */
    public function updateFeed($slug, $feed)
    {
        foreach ($feed->get_items(0, 1) as $item) {
            $lastPost = $item->get_date('Y-m-d H:i:s');
        }

        if (isset($lastPost)) {
            $lastFetch = date('Y-m-d H:i:s');

            $sql = "UPDATE news_feeds SET site_date_last_fetch = '{$lastFetch}', site_date_last_post = '{$lastPost}' WHERE site_slug = '{$slug}'";

            $this->db->query($sql);
        }
    }
}
