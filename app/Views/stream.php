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
      <ol class="links show">
      <?php foreach ($build as $row) :?>
        <li class="stream">
          <span class="stream__title">
            <a href="<?= $row->story_permalink; ?>" rel="noopener noreferrer" data-outgoing="<?= $row->story_hash; ?>"><?= htmlspecialchars_decode($row->story_title ?? '');
          if (htmlspecialchars_decode($row->story_title ?? '') === '') {
              echo '[missing title]';
          } ?></a>
          </span>
          <span class="ago--muted"><?php
          $time = Time::createFromFormat('Y-m-d H:i:s', $row->story_date, 'America/New_York');
          echo $time->humanize();
          ?> on <?= htmlspecialchars_decode($row->site_name ?? ''); ?></span>
        </li>
      <?php endforeach; ?>
      </ol>
    </div>
  </div>
</main>

<?= $this->include('includes/footer');
