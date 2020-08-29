<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * All interactions with aggro_* tables.
 */
class AggroModels extends Model {

  /**
   * Get single video.
   *
   * @param string $slug
   *   Video id.
   *
   * @return array
   *   Video data from table.
   */
  public function getVideo($slug) {
    $slug = esc($slug);
    $sql = "SELECT * FROM aggro_videos WHERE video_id='$slug' LIMIT 1";
    $query = $this->db->query($sql);
    return $query->getRowArray();
  }

  /**
   * Get all videos.
   *
   * @param string $sort
   *   - Recent.
   * @param string $range
   *   - Year.
   *   - Month.
   *   - Week.
   * @param string $perpage
   *   Results per page.
   * @param string $offset
   *   Result starting offset.
   *
   * @return string
   *   Video data from table.
   */
  public function getVideos($sort = 'recent', $range = 'month', $perpage = 10, $offset = 0) {
    $now = date("Y-m-d H:i:s");

    if ($sort == "recent") {
      $sortField = "aggro_date_added";
    }

    if ($range == "year") {
      $constrict = 'AND aggro_date_added BETWEEN DATE_SUB("' . $now . '", INTERVAL 365 DAY) AND DATE_SUB("' . $now . '", INTERVAL 30 MINUTE)';
    }

    if ($range == "month") {
      $constrict = 'AND aggro_date_added BETWEEN DATE_SUB("' . $now . '", INTERVAL 31 DAY) AND DATE_SUB("' . $now . '", INTERVAL 30 MINUTE)';
    }

    if ($range == "week") {
      $constrict = 'AND aggro_date_added BETWEEN DATE_SUB("' . $now . '", INTERVAL 7 DAY) AND DATE_SUB("' . $now . '", INTERVAL 30 MINUTE)';
    }

    $sql = 'SELECT * FROM aggro_videos WHERE flag_bad = 0 AND flag_archive = 0 AND aggro_date_updated <> "0000-00-00 00:00:00"' . $constrict . 'ORDER BY ' . $sortField . ' DESC LIMIT ' . $perpage . ' OFFSET ' . $offset;
    $query = $this->db->query($sql);
    return $query->getResult();
  }

  /**
   * Get all videos total.
   *
   * @return string
   *   Total number of active videos.
   */
  public function getVideosTotal() {
    $sql = 'SELECT aggro_id FROM aggro_videos WHERE flag_bad = 0 AND flag_archive = 0 AND aggro_date_updated <> "0000-00-00 00:00:00"';
    $query = $this->db->query($sql);
    return count($query->getResultArray());
  }

}
