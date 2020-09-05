<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * All interactions with news_* tables.
 */
class NewsModels extends Model {

  /**
   * Build featured, stream entries.
   *
   * @return bool
   *   Fetched true.
   */
  public function featuredBuilder() {
    $utilityModel = new UtilityModels();
    helper(['aggro', 'text']);

    $sql = "SELECT * FROM news_feeds WHERE flag_featured = 1 OR flag_stream = 1 ORDER BY site_name";
    $query = $this->db->query($sql);
    $featured = $query->getResult();
    $counter = 0;

    foreach ($featured as $row) {
      $fetch = fetch_feed($row->site_feed, $row->flag_spoof);
      $sql = "UPDATE news_feeds SET site_date_last_fetch='" . date('Y-m-d H:i:s') . "' WHERE site_id='" . $row->site_id . "'";
      $this->db->query($sql);
      $storyCount = 0;

      foreach ($fetch->get_items(0, 10) as $item) {
        if ($storyCount == 0) {
          $lastPost = $item->get_date('Y-m-d H:i:s');
          $sql = "UPDATE news_feeds SET site_date_last_post='" . $lastPost . "' WHERE site_id='" . $row->site_id . "'";
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
    return TRUE;
  }

  /**
   * Get featured page.
   *
   * @return array
   *   Featured page.
   */
  public function featuredPage() {
    $sql = "SELECT * FROM news_feeds WHERE flag_featured = 1 ORDER BY site_name";
    $query = $this->db->query($sql);

    $built = [];

    foreach ($query->getResult('array') as $row) {
      $counter = 1;
      $built[$row['site_slug']]['site_name'] = $row['site_name'];
      $built[$row['site_slug']]['site_slug'] = $row['site_slug'];
      $built[$row['site_slug']]['site_date_last_post'] = $row['site_date_last_post'];

      $innerSql = "SELECT * FROM news_featured WHERE site_id = " . $row['site_id'] . " ORDER BY story_date DESC LIMIT 3";
      $innerQuery = $this->db->query($innerSql);

      foreach ($innerQuery->getResult('array') as $innerRow) {
        $storyNum = "story" . $counter;
        $built[$row['site_slug']][$storyNum]['story_title'] = $innerRow['story_title'];
        $built[$row['site_slug']][$storyNum]['story_permalink'] = $innerRow['story_permalink'];
        $built[$row['site_slug']][$storyNum]['story_hash'] = $innerRow['story_hash'];
        $counter++;
      }
    }

    return $built;
  }

  /**
   * Get single site.
   *
   * @param string $slug
   *   Site slug.
   *
   * @return array
   *   Site data from table.
   */
  public function getSite($slug) {
    $slug = esc($slug);
    $sql = "SELECT * FROM news_feeds WHERE site_slug = '$slug' LIMIT 1";
    $query = $this->db->query($sql);
    return $query->getRowArray();
  }

  /**
   * Get all sites.
   *
   * @return object
   *   Site data from table.
   */
  public function getSites() {
    $sql = "SELECT * FROM news_feeds ORDER BY site_name";
    $query = $this->db->query($sql);
    return $query->getResult();
  }

  /**
   * Get recent directory updates.
   *
   * @return object
   *   All feed data from table.
   */
  public function getSitesRecent() {
    $sql = "SELECT * FROM news_feeds ORDER BY site_date_added DESC LIMIT 10";
    $query = $this->db->query($sql);
    return $query->getResult();
  }

  /**
   * Build stream page.
   *
   * @return array
   *   Stream page.
   */
  public function streamPage() {
    $sql = "SELECT news_feeds.site_name, news_feeds.site_slug, news_featured.story_title, news_featured.story_permalink, news_featured.story_date, news_featured.story_hash
            FROM news_featured
            INNER JOIN news_feeds
            ON news_featured.site_id = news_feeds.site_id
            ORDER BY news_featured.story_date DESC
            LIMIT 300";
    $query = $this->db->query($sql);
    return $query->getResult();
  }

  /**
   * Update feed data.
   *
   * @param string $slug
   *   Site slug (as ID).
   * @param object $feed
   *   Fetched feed object.
   */
  public function updateFeed($slug, $feed) {
    foreach ($feed->get_items(0, 1) as $item) {
      $lastPost = $item->get_date('Y-m-d H:i:s');
    }

    if (isset($lastPost)) {
      $lastFetch = date('Y-m-d H:i:s');

      $sql = "UPDATE news_feeds SET site_date_last_fetch = '$lastFetch', site_date_last_post = '$lastPost' WHERE site_slug = '$slug'";

      $this->db->query($sql);
    }
  }

}
