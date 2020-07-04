<?php

/**
 * @file
 * About page template.
 */

echo $this->include('includes/header'); ?>

<section class="block" role="main" id="content">
  <div class="row">
    <div class="twelve columns prose">
      <h1>Directory</h1>
      <p>This directory is filled with bmx-related sites that have RSS feeds. You can import all of these sites into your favorite feed reader with the <a href="/opml/">bmxfeed OPML file</a>.</p>
      <p>Did I miss a site? <a href="/submit/">Let me know</a>.</p>
    </div>
  </div>
</section>

<?php echo $this->include('includes/footer');
