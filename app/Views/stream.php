<?php

/**
 * @file
 * Stream page template.
 */

echo $this->include('includes/header'); ?>

<main role="main" id="content">
  <div class="row">
    <div class="twelve columns">
      <h1>Stream</h1>
    </div>
  </div>

  <div class="row">
    <div class="twelve columns">
      <ul>
      <?php foreach ($build as $row) :?>
        <li class="news-item">
          <span class="news-source">
            <a href="/sites/<?php echo $row->site_slug; ?>" ><?php echo htmlspecialchars($row->site_name); ?></a>
          </span>
          <span class="news-title">
            <a href="<?php echo $row->story_permalink; ?>" class="external" rel="noopener noreferrer" data-outgoing="<?php echo $row->story_hash; ?>"><?php echo $row->story_title; ?></a>
          </span>
          <span class="news-time timeago" title="<?php echo $row->story_date; ?>"></span>
        </li>
      <?php endforeach; ?>
      </ul>
    </div>
  </div>
</main>

<?php echo $this->include('includes/footer');
