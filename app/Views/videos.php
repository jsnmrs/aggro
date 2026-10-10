<?= /**
 * @file
 * Videos page template.
 */ $this->include('includes/header'); ?>

<main id="content" class="floor" tabindex="-1">
  <div class="wrap">
    <h1><?= esc($title); ?></h1>
  </div>

<?php if ($endpage === 0) { ?>
  <div class="wrap">
    <p>No videos found.</p>
  </div>
<?php } else { ?>
  <ul class="wrap" role="list">
<?php foreach ($build as $row) :?>
    <li class="box box--video">
      <a href="/video/<?= esc($row->video_id); ?>">
        <img src="/thumbs/<?= esc($row->video_id); ?>.webp" width="340" height="192" alt="">
        <p><?= videoTitle($row->video_title ?? null, $row->video_source_username ?? null); ?></p>
      </a>
    </li>
<?php endforeach; ?>
  </ul>
<?php } ?>

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
