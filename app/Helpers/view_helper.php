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

if (! function_exists('timeAgo')) {
    /**
     * Render a relative time inside a <time> element with an ISO 8601 datetime.
     *
     * @param string      $date     Date in Y-m-d H:i:s format
     * @param string      $timezone Timezone the date is stored in
     * @param string|null $class    Optional class attribute
     *
     * @return string
     */
    function timeAgo($date, $timezone, $class = null)
    {
        $time  = Time::createFromFormat('Y-m-d H:i:s', $date, $timezone);
        $attrs = $class === null ? '' : ' class="' . esc($class, 'attr') . '"';

        return '<time' . $attrs . ' datetime="' . esc($time->format('c')) . '">' . esc($time->humanize()) . '</time>';
    }
}

if (! function_exists('storyTitle')) {
    /**
     * Escaped story title, or a readable fallback that names the site for
     * screen reader users when the feed supplied no title.
     *
     * @param string|null $title
     * @param string|null $siteName
     *
     * @return string
     */
    function storyTitle($title, $siteName = null)
    {
        if (($title ?? '') !== '') {
            return esc($title);
        }

        $fallback = 'Untitled post';

        if (($siteName ?? '') !== '') {
            $fallback .= '<span class="visually-hidden"> on ' . esc($siteName) . '</span>';
        }

        return $fallback;
    }
}

if (! function_exists('videoTitle')) {
    /**
     * Escaped video title, or a readable fallback that names the source
     * channel when the video API supplied no title. Plain text only, so it
     * is safe in headings, link text, and title attributes alike.
     *
     * @param string|null $title
     * @param string|null $source
     *
     * @return string
     */
    function videoTitle($title, $source = null)
    {
        if (($title ?? '') !== '') {
            return esc($title);
        }

        $fallback = 'Untitled video';

        if (($source ?? '') !== '') {
            $fallback .= ' from ' . esc($source);
        }

        return $fallback;
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
            $title = storyTitle($story['story_title'] ?? null, $row['site_name'] ?? null);

            return '<li><a href="' . esc($story['story_permalink']) . '" rel="noopener noreferrer" data-outgoing="' . esc($story['story_hash']) . '">' . $title . '</a></li>';
        }
        if ($storyNum === 'story1') {
            return '<li>No recent posts</li>';
        }

        return '';
    }
}
