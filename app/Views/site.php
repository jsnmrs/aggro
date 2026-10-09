<?= /**
 * @file
 * Single site page template.
 */ $this->include('includes/header'); ?>

<main id="content" class="floor" tabindex="-1">
  <div class="wrap">
    <div class="full">
      <h1><?= esc($build['site_name']); ?></h1>
      <div class="meta">
        <p class="hug">Site: <a class="url" href="<?= esc($build['site_url']); ?>"><?= esc($build['site_url']); ?></a></p>
        <p class="hug">Feed: <a class="url" href="<?= esc($build['site_feed']); ?>"><?= esc($build['site_feed']); ?></a></p>
      </div>
      <h2>Recently on <?= esc($build['site_name']); ?></h2>
      <ul class="links" role="list">
      <?php if ($feedfetch->error) :?>
        <li>Unable to get <?= esc($build['site_name']); ?> feed.</li>
      <?php endif; ?>
      <?php foreach ($feedfetch->get_items(0, 10) as $item) :?>
        <li>
          <a href="<?= esc($item->get_permalink()); ?>" rel="noopener noreferrer">
            <?= esc(decode_entities($item->get_title())); ?>
          </a>
          <?= timeAgo($item->get_date('Y-m-d H:i:s'), 'America/New_York', 'ago--muted'); ?>
        </li>
      <?php endforeach; ?>
      </ul>
    </div>
  </div>
</main>

<?= $this->include('includes/footer');
