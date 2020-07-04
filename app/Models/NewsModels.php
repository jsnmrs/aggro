<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * All interactions with news_* tables.
 */
class NewsModels extends Model {

  /**
   * Build featured page.
   *
   * @return string
   *   Featured page.
   */
  public function featuredPage() {
    $sql = "SELECT * FROM feeds WHERE featured = 1 ORDER BY name";
    $query = $db->query($sql);

    foreach ($query->getResult('array') as $row) {
      $counter = 1;
      $built[$row->site_slug]['site_name'] = $row->site_name;
      $built[$row->site_slug]['site_slug'] = $row->site_slug;
      $built[$row->site_slug]['site_date_last_post'] = $row->site_date_last_post;

      $innerSql = "SELECT * FROM featured WHERE site_id = " . $row->id . " ORDER BY story_date DESC LIMIT 3";
      $innerQuery = $db->query($innerSql);

      foreach ($innerQuery->getResult('array') as $innerRow) {
        $storyNum = "story" . $counter;
        $built[$row->site_slug][$storyNum]['story_title'] = $innerRow->story_title;
        $built[$row->site_slug][$storyNum]['story_permalink'] = $innerRow->story_permalink;
        $built[$row->site_slug][$storyNum]['story_hash'] = $innerRow->story_hash;
        $counter++;
      }
    }

    return $built;
  }

  /**
   * Build stream page.
   *
   * @return string
   *   Stream page.
   */
  public function streamPage() {
    $sql = "SELECT feeds.name, feeds.slug, feeds.category, featured.title, featured.permalink, featured.pub_date, featured.permalink_sha FROM featured INNER JOIN feeds ON featured.site_id = feeds.id ORDER BY featured.pub_date DESC LIMIT 300";
    $query = $this->db->query($sql);
    return $query->result();
  }

  /**
   * Get popular links.
   *
   * @param string $range
   *   Day range.
   * @param string $limit
   *   Stories limit.
   *
   * @return string
   *   Popular links.
   */
  public function getPopularLinks($range = 1, $limit = 4) {
    $now = date('Y-m-d H:i:s');
    $sql = "SELECT COUNT(outgoing_link) AS outgoing_clicks, outgoing_link, outgoing_text, story_hash
            FROM outgoing
            WHERE outgoing_date > DATE_SUB('" . $now . "', INTERVAL " . $range . " DAY)
            GROUP BY outgoing_link
            ORDER BY COUNT(*) DESC
            LIMIT " . $limit;
    $query = $this->db->query($sql);
    return $query->result();
  }

  /**
   * Get single site.
   *
   * @param string $slug
   *   Site slug.
   *
   * @return string
   *   Site data from table.
   */
  public function getSingleSite($slug) {
    $slug = esc($slug);
    $sql = "SELECT * FROM news_feeds WHERE site_slug = '$slug' LIMIT 1";
    $query = $this->db->query($sql);
    return $query->getResult();
  }

}
