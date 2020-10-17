<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * All YouTube interactions with aggro_* tables.
 */
class YoutubeModels extends Model {

  public function parseChannel() {
    echo "parse channel feed.<br>";
    // is this using a helper before adding to DB??
    // Loop feed to find any video ids we don't have.
    // Add metadata for new videos to DB.
    // If upload date is > XX days mark video as archived.
  }

}
