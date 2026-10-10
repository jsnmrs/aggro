<?= /**
 * @file
 * Homepage template.
 */ $this->include('includes/header'); ?>

<main id="content" class="floor" tabindex="-1">
 <div class="wrap">
    <h1>Featured</h1>
 </div>

 <div class="wrap">
 <?php foreach ($build as $row) :?>
    <article class="box box--feature" aria-labelledby="site-<?= esc($row['site_slug'], 'attr'); ?>">
      <h2 id="site-<?= esc($row['site_slug'], 'attr'); ?>">
        <a href="/sites/<?= esc($row['site_slug']); ?>"><?= esc($row['site_name']); ?> <span class="visually-hidden">on BMXfeed</span></a>
      </h2>
      <p class="hug">Last post <?= timeAgo($row['site_date_last_post'], 'America/New_York', 'ago--muted'); ?></p>
      <ol class="links" role="list">
      <?php for ($story = 1; $story < 4; $story++) :?>
        <?php $storyNum = 'story' . $story; ?>
        <?= displayStory($row, $storyNum); ?>
      <?php endfor; ?>
      </ol>
    </article>
 <?php endforeach; ?>
 </div>

</main>

<?= $this->include('includes/footer');
