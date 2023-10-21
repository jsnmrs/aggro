<?php

/**
 * @file
 * Single video page template.
 */

use CodeIgniter\I18n\Time;

if ($build['video_height'] !== 0) {
    $ratio = round($build['video_height'] / $build['video_width'], 4);
}

echo $this->include('includes/header'); ?>

<main id="content" tabindex="-1">
  <div class="wrap">
    <div class="full">
      <h1><?= htmlspecialchars_decode($build['video_title'] ?? ''); ?></h1>
      <p>Spotted <span><?php
      $time = Time::createFromFormat('Y-m-d H:i:s', $build['aggro_date_added'], 'America/New_York');
echo $time->humanize();
?></span> via <a href="<?= $build['video_source_url']; ?>" rel="noopener noreferrer"><?= $build['video_source_username']; ?></a>.</p>
    </div>
  </div>

  <div class="curtain">
    <div class="randb">
      <div class="video<?php
if ($build['video_type'] === 'vimeo') {
    echo ' video--vimeo';
}
if ($build['video_type'] === 'youtube') {
    echo ' video--youtube';
}
?>" style="--aspect-ratio: <?= $ratio; ?>;">
        <?php if ($build['video_type'] === 'vimeo') :?>
          <iframe src="https://player.vimeo.com/video/<?= $build['video_id']; ?>?dnt=true&amp;portrait=0&amp;byline=0&amp;title=0&amp;autoplay=0&amp;color=ffffff" title="<?= $build['video_title']; ?> (embedded video)" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
        <?php endif; ?>
        <?php if ($build['video_type'] === 'youtube') :?>
          <iframe src="https://www.youtube.com/embed/<?= $build['video_id']; ?>?rel=0&amp;showinfo=0" title="<?= $build['video_title']; ?> (embedded video)" allowfullscreen></iframe>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>

<?= $this->include('includes/footer');
