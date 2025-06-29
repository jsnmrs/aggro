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
            <a href="<?= esc($row->story_permalink); ?>" rel="noopener noreferrer" data-outgoing="<?= esc($row->story_hash); ?>"><?= esc($row->story_title ?? '');
          if (($row->story_title ?? '') === '') {
              echo '[missing title]';
          } ?></a>
          </span>
          <span class="ago--muted"><?php
          $time = Time::createFromFormat('Y-m-d H:i:s', $row->story_date, 'America/New_York');
          echo $time->humanize();
          ?> on <?= esc($row->site_name ?? ''); ?></span>
        </li>
      <?php endforeach; ?>
      </ol>
    </div>
  </div>
</main>

<?= $this->include('includes/footer');
