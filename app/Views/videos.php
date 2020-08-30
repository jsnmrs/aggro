<?php

/**
 * @file
 * Videos page template.
 */

echo $this->include('includes/header'); ?>

<main id="content">
  <div class="wrap player">
    <h1>Recent Videos</h1>
  </div>

<?php
$counter = 0;
foreach ($build as $row) {
  if ($counter == 0 || ($counter % 4 == 0)) { ?>
  <div class="wrap">
  <?php } ?>
    <div class="videobox">
      <a href="/video/<?php echo $row->video_id; ?>">
        <img src="/thumbs/<?php echo $row->video_id; ?>.jpg" alt="">
        <p><?php echo $row->video_title; ?></p>
      </a>
    </div>
  <?php if ($counter != 0 && (($counter + 1) % 4 == 0)) { ?>
  </div>
  <?php }
  $counter++;
}
?>

<?php if ($page != $endpage) { ?>
  <div class="wrap scroll-nav">
    <a id="next" class="btn" href="/video/<?php echo $sort; ?>/<?php echo $page + 1; ?>/">See more videos</a>
  </div>
<?php } ?>
</main>

<?php echo $this->include('includes/footer');
