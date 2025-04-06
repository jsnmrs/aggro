<?= /**
 * @file
 * Videos page template.
 */ $this->include('includes/header'); ?>

<main id="content" class="floor" tabindex="-1">
  <div class="wrap">
    <h1>Recent Videos<?php
    if ($page >= 2) {
        echo ' ' . $page . ' of ' . $endpage;
    } ?></h1>
  </div>

  <div class="wrap">
<?php if ($endpage === 0) { ?>
    <p>No videos found.</p>
<?php } ?>
<?php foreach ($build as $row) :?>
    <div class="box box--video">
      <a href="/video/<?= $row->video_id; ?>">
        <img src="/thumbs/<?= $row->video_id; ?>.webp" width="340" height="192" alt="">
        <p><?= htmlspecialchars_decode($row->video_title ?? ''); ?></p>
      </a>
    </div>
<?php endforeach; ?>
  </div>

<?php if ($page !== $endpage && $endpage !== 0) { ?>
  <div class="wrap">
    <a href="/video/<?= $sort; ?>/<?= $page + 1; ?>" class="cta">Jump to page <?= $page + 1; ?></a>
  </div>
<?php } ?>
</main>

<?= $this->include('includes/footer');
