<?php

/**
 * @file
 * Log template.
 */

echo $this->include('includes/header'); ?>

<main id="content">
  <div class="wrap">
    <div class="full">
      <h1><?php echo $title; ?></h1>
      <ul>
  <?php if (is_array($build) || is_object($build)) :?>
    <?php foreach ($build as $row) :?>
      <?php if (is_object($row)) :?>
      <li><?php echo $row->log_message; ?> <span class="ago" title="<?php echo $row->log_date; ?>"></span></li>
      <?php endif; ?>
      <?php if (is_string($row)) :?>
        <li><?php echo $row; ?></li>
      <?php endif; ?>
    <?php endforeach; ?>
  <?php endif; ?>
      </ul>
    </div>
  </div>
</main>

<?php echo $this->include('includes/footer');
