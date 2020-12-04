<?php

/**
 * @file
 * Stream page template.
 */

echo $this->include('includes/header'); ?>

<main id="content" class="floor" tabindex="-1">
  <div class="wrap">
    <h1>Stream</h1>
  </div>

  <div class="wrap">
    <div class="full">
      <ul class="stream links">
      <?php foreach ($build as $row) :?>
        <li class="stream__item">
          <span class="stream__source">
            <a href="/sites/<?php echo $row->site_slug; ?>" ><?php echo htmlspecialchars_decode($row->site_name); ?></a>
          </span>
          <span class="ago ago--muted" data-date="<?php echo $row->story_date; ?>"></span>
          <span class="stream__title">
            <a href="<?php echo $row->story_permalink; ?>" rel="noopener noreferrer" data-outgoing="<?php echo $row->story_hash; ?>"><?php echo htmlspecialchars_decode($row->story_title);
            if (htmlspecialchars_decode($row->story_title) == "") {
              echo "[missing title]";
            } ?></a>
          </span>
        </li>
      <?php endforeach; ?>
      </ul>
    </div>
  </div>
</main>

<?php echo $this->include('includes/footer');
