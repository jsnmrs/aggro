<?php

/**
 * @file
 * Stream page template.
 */

use CodeIgniter\I18n\Time;

echo $this->include('includes/header'); ?>

<main id="content" class="floor" tabindex="-1">
  <div class="wrap">
    <h1>Stream</h1>
  </div>

  <div class="wrap">
    <div class="full">
      <ul class="links">
      <?php foreach ($build as $row) :?>
        <li class="stream">
          <span class="stream__source">
            <a href="/sites/<?= $row->site_slug; ?>" ><?= htmlspecialchars_decode($row->site_name ?? ''); ?></a>
          </span>
          <span class="ago--muted"><?php
          $time = Time::createFromFormat('Y-m-d H:i:s', $row->story_date, 'America/New_York');
          echo $time->humanize();
          ?></span>
          <span class="stream__title">
            <a href="<?= $row->story_permalink; ?>" rel="noopener noreferrer" data-outgoing="<?= $row->story_hash; ?>"><?= htmlspecialchars_decode($row->story_title ?? '');
          if (htmlspecialchars_decode($row->story_title ?? '') === '') {
              echo '[missing title]';
          } ?></a>
          </span>
        </li>
      <?php endforeach; ?>
      </ul>
    </div>
  </div>
</main>

<?= $this->include('includes/footer');
