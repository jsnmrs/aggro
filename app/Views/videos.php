<?php

/**
 * @file
 * Videos page template.
 */

echo $this->include('includes/header'); ?>

<main id="content" class="floor" tabindex="-1">
  <div class="wrap">
    <h1>Recent Videos<?php
    if ($page >= 2) {
      echo " " . $page . " of " . $endpage;
    } ?></h1>
  </div>

  <div class="wrap">
<?php if ($endpage == 0) { ?>
    <p>No videos found.</p>
<?php } ?>
<?php foreach ($build as $row) :?>
    <div class="box box--video">
      <a href="/video/<?php echo $row->video_id; ?>">
        <img src="/thumbs/<?php echo $row->video_id; ?>.jpg" width="340" height="192" alt="">
        <p><?php echo htmlspecialchars_decode($row->video_title); ?></p>
      </a>
    </div>
<?php endforeach; ?>
  </div>

<?php if ($page != $endpage && $endpage != 0) { ?>
  <div class="wrap">
    <a href="/video/<?php echo $sort; ?>/<?php echo $page + 1; ?>" class="cta">Jump to page <?php echo $page + 1; ?></a>
  </div>
<?php } ?>
</main>

<?php echo $this->include('includes/footer');
