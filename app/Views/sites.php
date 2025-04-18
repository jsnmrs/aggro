<?= /**
 * @file
 * Directory (sites) page template.
 */ $this->include('includes/header'); ?>

<main id="content" class="floor" tabindex="-1">
  <div class="wrap">
    <div class="full">
      <h1>Directory</h1>
      <p>This directory is filled with bmx-related sites that have RSS feeds. You can import all of these sites into your favorite feed reader with the <a href="/opml">bmxfeed OPML file</a>.</p>

      <ul class="columns links">
        <?php foreach ($build as $siteResult) :?>
        <li><a href="/sites/<?= $siteResult->site_slug; ?>"><?= $siteResult->site_name; ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</main>

<?= $this->include('includes/footer');
