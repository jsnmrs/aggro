<?php

/**
 * @file
 * Stream page template.
 */

echo $this->include('includes/header'); ?>

<main id="content">
  <div class="wrap">
    <h1>Stream</h1>
  </div>

  <div class="wrap">
    <ul>
    <?php foreach ($build as $row) :?>
      <li class="news-item">
        <span class="news-source">
          <a href="/sites/<?php echo $row->site_slug; ?>" ><?php echo htmlspecialchars($row->site_name); ?></a>
        </span>
        <span class="news-title">
          <a href="<?php echo $row->story_permalink; ?>" class="external" rel="noopener noreferrer" data-outgoing="<?php echo $row->story_hash; ?>"><?php echo htmlspecialchars_decode($row->story_title); ?></a>
        </span>
        <span class="news-time timeago" title="<?php echo $row->story_date; ?>"></span>
      </li>
    <?php endforeach; ?>
    </ul>
  </div>
</main>

<?php echo $this->include('includes/footer');
