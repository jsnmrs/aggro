<?= /**
 * @file
 * Stream page template.
 */ $this->include('includes/header'); ?>

<main id="content" class="floor" tabindex="-1">
  <div class="wrap">
    <h1>Stream</h1>
  </div>

  <div class="wrap">
    <div class="full">
      <ol class="links show">
      <?php foreach ($build as $row) :?>
        <li class="stream">
          <span class="stream__title">
            <a href="<?= esc($row->story_permalink); ?>" rel="noopener noreferrer" data-outgoing="<?= esc($row->story_hash); ?>"><?= storyTitle($row->story_title ?? null, $row->site_name ?? null); ?></a>
          </span>
          <span class="ago--muted"><?= timeAgo($row->story_date, 'America/New_York'); ?> on <?= esc($row->site_name ?? ''); ?></span>
        </li>
      <?php endforeach; ?>
      </ol>
    </div>
  </div>
</main>

<?= $this->include('includes/footer');
