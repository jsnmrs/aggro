<?php

/**
 * @file
 * About page template.
 */

echo $this->include('includes/header'); ?>

<main id="content">
  <div class="wrap">
    <div class="full">
      <h1>About BMXfeed</h1>
      <p>Launched in late 2006, BMXfeed is a bmx news and video aggregator (robot). The site finds and adds new blog posts and videos 24/7.</p>

      <p>The code that runs BMXfeed is called aggro. The entire <a href="https://github.com/jsnmrs/aggro">aggro codebase is open source</a>.</p>

      <p><a href="/submit/">Send me a note</a> if you have a site or video to submit.</p>

      <p>I&rsquo;m Jason. I built and maintain BMXfeed. See more of my work at <a href="https://jasonmorris.com/">jasonmorris.com</a>.</p>
    </div>
  </div>
</main>

<?php echo $this->include('includes/footer');
