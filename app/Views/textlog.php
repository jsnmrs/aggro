<?= esc($title) . "\n\n";

if (is_array($build) || is_object($build)) {
    foreach ($build as $row) {
        if (is_object($row)) {
            echo esc($row->log_date) . "\n";
            echo esc($row->log_message) . "\n\n";
        }
        if (is_string($row)) {
            echo esc($row) . "\n\n";
        }
    }
}
