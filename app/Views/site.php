<?php

/**
 * @file
 * Single site page template.
 */

use CodeIgniter\I18n\Time;

echo $this->include('includes/header'); ?>

<main id="content" class="floor" tabindex="-1">
  <div class="wrap">
    <div class="full">
      <h1><?= esc($build['site_name']); ?></h1>
      <p class="hug">Site: <a href="<?= esc($build['site_url']); ?>"><?= esc($build['site_url']); ?></a></p>
      <p class="hug">Feed: <a href="<?= esc($build['site_feed']); ?>"><?= esc($build['site_feed']); ?></a></p>
      <h2>Recently on <?= esc($build['site_name']); ?></h2>
      <ul class="links">
      <?php if ($feedfetch->error) :?>
        <li>Unable to get <?= esc($build['site_name']); ?> feed.</li>
      <?php endif; ?>
      <?php foreach ($feedfetch->get_items(0, 10) as $item) :?>
        <li>
          <a href="<?= esc($item->get_permalink()); ?>" rel="noopener noreferrer">
            <?= esc(html_entity_decode($item->get_title(), ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?>
          </a>
          <span class="ago--muted"><?php
          $tempDate = $item->get_date('Y-m-d H:i:s');
          $time     = Time::createFromFormat('Y-m-d H:i:s', $tempDate, 'America/New_York');
          echo $time->humanize();
          ?></span>
        </li>
      <?php endforeach; ?>
      </ul>
    </div>
  </div>
</main>

<?= $this->include('includes/footer');
