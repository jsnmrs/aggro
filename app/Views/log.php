<?php

/**
 * @file
 * Log template.
 */

echo $this->include('includes/header'); ?>

<main id="content">
  <div class="wrap">
    <h1>Log</h1>
    <ul>
<?php foreach ($build as $row) :?>
  <li><?php echo $row->log_message; ?> <span class="timeago" title="<?php echo $row->log_date; ?>"></span></li>
<?php endforeach; ?>
    </ul>
  </div>
</main>

<?php echo $this->include('includes/footer');
