<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * All interactions with news_* tables.
 */
class NewsModels extends Model {

  /**
   * Get all sites.
   *
   * @return object
   *   Site data from table.
   */
  public function getAllSites() {
    $sql = "SELECT * FROM news_feeds ORDER BY site_name";
    $query = $this->db->query($sql);
    return $query->getResult();
  }

  /**
   * Get single site.
   *
   * @param string $slug
   *   Site slug.
   *
   * @return object
   *   Site data from table.
   */
  public function getSingleSite($slug) {
    $slug = esc($slug);
    $sql = "SELECT * FROM news_feeds WHERE site_slug = '$slug' LIMIT 1";
    $query = $this->db->query($sql);
    return $query->getRowArray();
  }

  /**
   * Build featured page.
   *
   * @return array
   *   Featured page.
   */
  public function featuredPage() {
    $sql = "SELECT * FROM news_feeds WHERE flag_featured = 1 ORDER BY site_name";
    $query = $this->db->query($sql);

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
   * Get all updates.
   *
   * @return object
   *   All feed data from table.
   */
  public function getAllUpdates() {
    $sql = "SELECT * FROM news_feeds ORDER BY site_date_added DESC LIMIT 10";
    $query = $this->db->query($sql);
    return $query->getResult();
  }

}
