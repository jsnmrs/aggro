<?php

/**
 * @file
 * Homepage template.
 */

echo $this->include('includes/header'); ?>

<section class="block" role="main" id="content">
  <div class="row">
    <div class="twelve columns">
      <h1>News</h1>
    </div>
  </div>
  <div class="row">
    <div class="six columns">
      <h2>Popular today</h2>
    </div>
    <div class="six columns">
      <h2>Popular this week</h2>
    </div>
  </div>
  <div class="row">
    <div class="twelve columns feature-video">
      <h2><a href="/video/">Recent videos</a></h2>
      <div class="row">
      </div>
    </div>
  </div>

  <div class="row">
  <?php foreach ($built as $row) :?>
    <div class="six columns feature" role="article">
      <h2>
        <a href="/sites/<?php echo $row['site_slug']; ?>"><?php echo $row['site_name']; ?></a>
        <span class="timeago" title="<?php echo $row['site_date_last_post']; ?>"></span>
      </h2>
      <ol>
      <?php for ($story = 1; $story < 4; $story++) :?>
        <?php $story_num = "story" . $story; ?>
        <?php if (isset($row[$story_num])) :?>
        <li><a href="/go/<?php echo $row[$story_num]['story_hash'] ?>" class="external" rel="nofollow"><?php echo $row[$story_num]['story_title'] ?></a></li>
        <?php endif; ?>
        <?php if (!isset($row[$story_num]) && $story == 1) :?>
        <li>No recent posts</li>
        <?php endif; ?>
      <?php endfor; ?>
      </ol>
    </div>
  <?php endforeach; ?>
  </div>

</section>

<?php echo $this->include('includes/footer');
