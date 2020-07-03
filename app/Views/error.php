<?php

/**
 * @file
 * Error page (404) template.
 */

echo $this->include('includes/header'); ?>

<section class="block" role="main" id="content">
  <div class="row">
    <div class="twelve columns">
      <h2>Page not found</h2>
      <p>We can’t find the page you were looking for.</p>
      <p>Head back to the <a href="/">bmxfeed homepage</a> and maybe you’ll find what you’re looking from there.</p>
    </div>
  </div>
</section>

<?php echo $this->include('includes/footer');
