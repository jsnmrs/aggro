<?php

/**
 * @file
 * Single video page template.
 */

$vidwidth = 1024;
if ($build['video_height'] != 0) {
  $vidheight = round($build['video_height'] / $build['video_width'] * $vidwidth);
  $ratio = $vidwidth / $vidheight;
}

echo $this->include('includes/header'); ?>

<main id="content">
  <div class="wrap">
    <div class="full">
      <h1><?php echo htmlspecialchars_decode($build['video_title']); ?></h1>
      <p>Spotted <span class="ago" title="<?php echo $build['aggro_date_added']; ?>"></span> via <a href="<?php echo $build['video_source_url']; ?>" rel="noopener noreferrer"><?php echo $build['video_source_username']; ?></a>.</p>
    </div>
  </div>

  <div class="video__outer">
    <div class="wrap">
      <div class="video<?php
      if ($ratio > 1.5) {
        echo " video--wide";
      }
      if ($build['video_type'] == "vimeo") {
        echo " video--vimeo";
      }
      if ($build['video_type'] == "youtube") {
        echo " video--youtube";
      }
      ?>">
        <?php if ($build['video_type'] == "vimeo") :?>
          <iframe src="https://player.vimeo.com/video/<?php echo $build['video_id']; ?>?dnt=true&amp;portrait=0&amp;byline=0&amp;title=0&amp;autoplay=0&amp;color=ffffff" frameborder="0" title="<?php echo htmlspecialchars_decode($build['video_title']); ?>" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
        <?php endif; ?>
        <?php if ($build['video_type'] == "youtube") :?>
          <iframe src="https://www.youtube-nocookie.com/embed/<?php echo $build['video_id']; ?>?rel=0&amp;showinfo=0" frameborder="0" title="<?php echo htmlspecialchars_decode($build['video_title']); ?>" allowfullscreen></iframe>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>

<?php echo $this->include('includes/footer');
