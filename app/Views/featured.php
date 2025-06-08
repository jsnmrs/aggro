<?=/**
 * @file
 * Homepage template.
 */ $this->include('includes/header'); ?>

<main id="content" class="floor" tabindex="-1">
 <div class="wrap">
    <h1>News</h1>
 </div>

 <div class="wrap">
 <?php foreach ($build as $row) :?>
    <article class="box box--feature">
      <h2>
        <a href="/sites/<?= esc($row['site_slug']); ?>"><?= esc($row['site_name']); ?></a>
        <span class="ago--muted"><?= humanizeTime($row['site_date_last_post'], 'America/New_York'); ?></span>
      </h2>
      <ol class="links">
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
