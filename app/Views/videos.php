<?php

/**
 * @file
 * Videos page template.
 */

echo $this->include('includes/header'); ?>

<main id="content" class="floor">
  <div class="wrap">
    <h1>Recent Videos</h1>
  </div>

  <div class="wrap">
<?php foreach ($build as $row) :?>
    <div class="box box--video">
      <a href="/video/<?php echo $row->video_id; ?>">
        <img src="/thumbs/<?php echo $row->video_id; ?>.jpg" alt="">
        <p><?php echo htmlspecialchars_decode($row->video_title); ?></p>
      </a>
    </div>
<?php endforeach; ?>
  </div>

<?php if ($page != $endpage) { ?>
  <div class="wrap">
    <a href="/video/<?php echo $sort; ?>/<?php echo $page + 1; ?>/">See more videos</a>
  </div>
<?php } ?>
</main>

<?php echo $this->include('includes/footer');
