<?php

/**
 * @file
 * Homepage template.
 */

use CodeIgniter\I18n\Time;

echo $this->include('includes/header'); ?>

<main id="content" class="floor" tabindex="-1">
 <div class="wrap">
    <h1>News</h1>
 </div>

 <div class="wrap">
 <?php foreach ($build as $row) :?>
    <article class="box box--feature">
      <h2>
        <a href="/sites/<?= $row['site_slug']; ?>"><?= $row['site_name']; ?></a>
        <span class="ago--muted"><?= humanizeTime($row['site_date_last_post'], 'America/New_York'); ?></span>
      </h2>
      <ol class="links">
      <?php for ($story = 1; $story < 4; $story++) :?>
        <?php $storyNum = 'story' . $story; ?>
        <?= displayStory($row, $storyNum); ?>
      <?php endfor; ?>
      </ol>
    </article>
 <?php endforeach; ?>
 </div>

</main>

<?= $this->include('includes/footer');

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
        $title = htmlspecialchars_decode($story['story_title'] ?? '');
        $title = $title === '' ? '[missing title]' : $title;

        return "<li><a href=\"{$story['story_permalink']}\" rel=\"noopener noreferrer\" data-outgoing=\"{$story['story_hash']}\">{$title}</a></li>";
    }
    if ($storyNum === 'story1') {
        return '<li>No recent posts</li>';
    }

    return '';
}
