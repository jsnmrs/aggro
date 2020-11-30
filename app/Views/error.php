<?php

/**
 * @file
 * Error page (404) template.
 */

echo $this->include('includes/header'); ?>

<main id="content" class="floor">
  <div class="wrap">
    <div class="full">
      <h1>Page not found</h1>
      <p>We can’t find the page you were looking for.</p>
      <p>Head back to the <a href="/">bmxfeed homepage</a> and maybe you’ll find what you’re looking from there.</p>
    </div>
  </div>
</main>

<?php echo $this->include('includes/footer');
