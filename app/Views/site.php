<?php

/**
 * @file
 * About page template.
 */

echo $this->include('includes/header'); ?>

<section class="block" role="main" id="content">
  <div class="row">
    <div class="twelve columns prose">
      <h1><?php echo $site['site_name']; ?></h1>
      <p>Site: <a href="<?php echo $site['site_url']; ?>"><?php echo $site['site_url']; ?></a><br>
      Feed: <a href="<?php echo $site['site_feed']; ?>"><?php echo $site['site_feed']; ?></a></p>
      <h2>Recently on <?php echo $site['site_name']; ?></h2>
    </div>
  </div>
</section>

<?php echo $this->include('includes/footer');
