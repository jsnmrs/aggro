<?php

/**
 * View Helper Functions
 *
 * Helper functions for view rendering and formatting.
 */

use CodeIgniter\I18n\Time;

if (! function_exists('humanizeTime')) {
    /**
     * Humanize the time for a given date.
     *
     * @param string $date
     * @param string $timezone
     *
     * @return string
     */
    function humanizeTime($date, $timezone)
    {
        $time = Time::createFromFormat('Y-m-d H:i:s', $date, $timezone);

        return $time->humanize();
    }
}

if (! function_exists('displayStory')) {
    /**
     * Display a story link if it exists, otherwise display a message.
     *
     * @param array  $row
     * @param string $storyNum
     *
     * @return string
     */
    function displayStory($row, $storyNum)
    {
        if (isset($row[$storyNum])) {
            $story = $row[$storyNum];
            $title = $story['story_title'] ?? '';
            $title = $title === '' ? '[missing title]' : $title;

            return '<li><a href="' . esc($story['story_permalink']) . '" rel="noopener noreferrer" data-outgoing="' . esc($story['story_hash']) . '">' . esc($title) . '</a></li>';
        }
        if ($storyNum === 'story1') {
            return '<li>No recent posts</li>';
        }

        return '';
    }
}
