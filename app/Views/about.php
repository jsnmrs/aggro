<?php

/**
 * @file
 * About page template.
 */

echo $this->include('includes/header'); ?>

<main id="content">
  <div class="wrap prose">
    <h1>About BMXfeed</h1>
    <p>Launched in early 2006, BMXfeed has grown from a directory of links to a news and video aggregator for all things bmx.</p>
    <p>BMXfeed is updated automatically with news and videos from all over the bmx world. There are no ads and no filters. It&rsquo;s better that way.</p>
    <p><a href="/submit/">Get in touch if you have a site or video to submit</a>.</p>
    <p>I&rsquo;m Jason. I built and maintain BMXfeed. See more of my work at <a href="https://jasonmorris.com/">jasonmorris.com</a>.</p>
  </div>
</main>

<?php echo $this->include('includes/footer');
