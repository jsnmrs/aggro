<?php

/**
 * @file
 * Single video page template.
 */
$videoWidth  = (int) ($build['video_width'] ?? 0);
$videoHeight = (int) ($build['video_height'] ?? 0);
$ratio       = ($videoWidth > 0 && $videoHeight > 0)
    ? round($videoHeight / $videoWidth, 4)
    : 0.5625; // 16:9 fallback keeps layout intact for zero-sized records
$videoTitle = videoTitle($build['video_title'] ?? null, $build['video_source_username'] ?? null);
// A direct link to the source video is the fallback when the embed is
// blocked, and the only reliable route to the uploader's captions.
$watchSite = $build['video_type'] === 'vimeo' ? 'Vimeo' : 'YouTube';
$watchUrl  = $build['video_type'] === 'vimeo'
    ? 'https://vimeo.com/' . esc($build['video_id'])
    : 'https://www.youtube.com/watch?v=' . esc($build['video_id']);

echo $this->include('includes/header'); ?>

<main id="content" tabindex="-1">
  <div class="wrap">
    <div class="full">
      <h1><?= $videoTitle; ?></h1>
      <p>Spotted <?= timeAgo($build['aggro_date_added'], 'America/New_York'); ?> via <a href="<?= esc($build['video_source_url']); ?>" rel="noopener noreferrer"><?= esc($build['video_source_username']); ?></a>.</p>
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
?>" style="--aspect-ratio: <?= esc($ratio); ?>;">
        <?php if ($build['video_type'] === 'vimeo') :?>
          <iframe src="https://player.vimeo.com/video/<?= esc($build['video_id']); ?>?dnt=true&amp;portrait=0&amp;byline=0&amp;title=0&amp;autoplay=0&amp;color=ffffff" title="<?= $videoTitle; ?> (embedded video)" allow="fullscreen; picture-in-picture" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
        <?php endif; ?>
        <?php if ($build['video_type'] === 'youtube') :?>
          <iframe src="https://www.youtube.com/embed/<?= esc($build['video_id']); ?>?rel=0&amp;showinfo=0" title="<?= $videoTitle; ?> (embedded video)" allow="encrypted-media; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="wrap">
    <p><a href="<?= $watchUrl; ?>" rel="noopener noreferrer">Watch <span class="visually-hidden"><?= $videoTitle; ?> </span>on <?= $watchSite; ?></a> for captions and the full description.</p>
  </div>
</main>

<?= $this->include('includes/footer');
