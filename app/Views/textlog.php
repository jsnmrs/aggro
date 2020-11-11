<?php

/**
 * @file
 * Log template.
 */

echo "<h1>" . $title . "</h1>\n\n";

if (is_array($build) || is_object($build)) {
  foreach ($build as $row) {
    if (is_object($row)) {
      echo $row->log_date . "\n";
      echo $row->log_message . "\n";
      echo "<br><hr>\n";
    }
    if (is_string($row)) {
      echo $row;
      echo "<br><hr>\n\n";
    }
  }
}
