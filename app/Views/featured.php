<?php

/**
 * @file
 * Homepage template.
 */

echo $this->include('includes/header'); ?>

<main id="content">
  <div class="wrap">
    <h1>News</h1>
  </div>

  <div class="wrap">
  <?php foreach ($build as $row) :?>
    <article class="featurebox">
      <h2>
        <a href="/sites/<?php echo $row['site_slug']; ?>"><?php echo $row['site_name']; ?></a>
        <span class="timeago" title="<?php echo $row['site_date_last_post']; ?>"></span>
      </h2>
      <ol>
      <?php for ($story = 1; $story < 4; $story++) :?>
        <?php $story_num = "story" . $story; ?>
        <?php if (isset($row[$story_num])) :?>
        <li><a href="<?php echo $row[$story_num]['story_permalink']; ?>" class="external" rel="noopener noreferrer" data-outgoing="<?php echo $row[$story_num]['story_hash']; ?>"><?php echo htmlspecialchars_decode($row[$story_num]['story_title']); ?></a></li>
        <?php endif; ?>
        <?php if (!isset($row[$story_num]) && $story == 1) :?>
        <li>No recent posts</li>
        <?php endif; ?>
      <?php endfor; ?>
      </ol>
    </article>
  <?php endforeach; ?>
  </div>

</main>

<?php echo $this->include('includes/footer');
