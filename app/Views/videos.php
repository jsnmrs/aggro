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
      <a href="/video/<?= esc($row->video_id); ?>">
        <img src="/thumbs/<?= esc($row->video_id); ?>.webp" width="340" height="192" alt="">
        <p><?= esc($row->video_title ?? ''); ?></p>
      </a>
    </div>
<?php endforeach; ?>
  </div>

<?php if ($endpage > 1) { ?>
  <nav class="wrap" aria-label="Pagination">
    <?php if ($page > 1) { ?>
      <a href="<?= $page === 2 ? '/video' : '/video/' . esc($sort) . '/' . ($page - 1); ?>" class="cta">Back to page <?= $page - 1; ?></a>
    <?php } ?>
    <?php if ($page !== $endpage) { ?>
      <a href="/video/<?= esc($sort); ?>/<?= $page + 1; ?>" class="cta">Jump to page <?= $page + 1; ?></a>
    <?php } ?>
  </nav>
<?php } ?>
</main>

<?= $this->include('includes/footer');
