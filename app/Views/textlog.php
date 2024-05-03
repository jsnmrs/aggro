<?= $title . "\n\n";

if (is_array($build) || is_object($build)) {
    foreach ($build as $row) {
        if (is_object($row)) {
            echo $row->log_date . "\n";
            echo $row->log_message . "\n\n";
        }
        if (is_string($row)) {
            echo $row . "\n\n";
        }
    }
}
