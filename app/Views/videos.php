<?php

/**
 * @file
 * Videos page template.
 */

echo $this->include('includes/header'); ?>

<section class="block" role="main" id="content">
  <div class="row">
    <div class="twelve columns player">
      <h1>Recent Videos</h1>
    </div>
  </div>

    <div id="videocontent">
      <div class="row videogrid">
        <div class="twelve columns feature-video">
<?php
$counter = 0;
foreach ($build as $row) {
  if ($counter == 0 || ($counter % 4 == 0)) { ?>
    <div class="row">
  <?php } ?>
      <div class="three columns videobox">
        <a href="/video/<?php echo $row->video_id; ?>">
          <img src="/thumbs/<?php echo $row->video_id; ?>.jpg" alt="<?php echo htmlspecialchars($row->video_title); ?>">
        </a>
        <p><a href="/video/<?php echo $row->video_id; ?>"><?php echo $row->video_title; ?></a></p>
      </div>
  <?php if ($counter != 0 && (($counter + 1) % 4 == 0)) { ?>
    </div>
  <?php }
  $counter++;
}
?>
      </div>
    </div>

<?php if ($page != $endpage) { ?>
    <div class="row">
      <div class="twelve columns scroll-nav">
        <a id="next" class="btn" href="/video/<?php echo $sort; ?>/<?php echo $page + 1; ?>/">See more videos</a>
      </div>
    </div>
<?php } ?>
  </div>
</section>

</section>

<?php echo $this->include('includes/footer');
