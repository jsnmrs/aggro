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
      <ul>
      <?php if ($feedfetch->error) :?>
        <li>Unable to get <?php echo $site['site_name']; ?> feed.</li>
      <?php endif; ?>
      <?php foreach ($feedfetch->get_items(0, 10) as $item) :?>
        <li>
          <a href="<?php echo $item->get_permalink(); ?>" class="external">
            <?php echo $item->get_title(); ?>
          </a>
          <span class="timeago" title="<?php echo $item->get_date('Y-m-d\TH:i:sO'); ?>"></span>
        </li>
      <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>

<?php echo $this->include('includes/footer');
