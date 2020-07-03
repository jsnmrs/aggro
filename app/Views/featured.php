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
    <div class="twelve columns">
      <p>feature page</p>
    </div>
  </div>
</section>

<?php echo $this->include('includes/footer');
