<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * @file
 * All utility functions.
 */

/**
 * All models for utilities.
 */
class UtilityModels extends Model {

  /**
   * Get log entries from aggro_log.
   *
   * @return string
   *   Recent log entries.
   */
  public function getLog() {
    $sql = "SELECT * FROM aggro_log ORDER BY log_date DESC LIMIT 250";
    $query = $this->db->query($sql);
    return $query->getResult();
  }

  /**
   * Send message to aggro_log table. Typically non-error messages.
   *
   * @param string $message
   *   Message to insert into aggro_log table.
   *
   * @return bool
   *   Message inserted into aggro_log table.
   */
  public function sendLog($message) {
    $sql = "INSERT INTO aggro_log (log_date, log_message)
            VALUES ('" . date('Y-m-d H:i:s') . "', '" . $message . "')";
    $this->db->query($sql);
    return TRUE;
  }

}
