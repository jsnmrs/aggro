<?php

/**
 * @file
 * Log template.
 */

echo $this->include('includes/header'); ?>

<section class="block" role="main" id="content">
  <div class="row">
    <div class="twelve columns">
      <h1>Log</h1>
      <ul>
  <?php foreach ($build as $row) :?>
    <li><?php echo $row->log_message; ?> <span class="timeago" title="<?php echo $row->log_date; ?>"></span></li>
  <?php endforeach; ?>
      </ul>
  </div>

</section>

<?php echo $this->include('includes/footer');
