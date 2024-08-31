<?php

/**
 * @file
 * Site footer include.
 */

use CodeIgniter\I18n\Time;

?>
<div class="floor">
  <div class="wrap">
    <footer>
      <p class="tagline">BMXfeed <span class="ago--muted"><?php
      $time = Time::createFromFormat('Y-m-d H:i:s', '2006-12-24 12:00:00', 'America/New_York');
echo $time->humanize();
?></span></p>
    </footer>
    <nav aria-label="Footer">
      <ul class="nav nav--bottom">
        <li><a href="/"
          <?php if ($slug === 'featured') {
              echo ' aria-current="page"';
          }
?>>News</a></li>
        <li><a href="/stream"
          <?php if ($slug === 'stream') {
              echo ' aria-current="page"';
          }
?>>Stream</a></li>
        <li><a href="/video"
          <?php if ($slug === 'video') {
              echo ' aria-current="page"';
          }
?>>Videos</a></li>
        <li><a href="/watch"
          <?php if ($slug === 'watch') {
              echo ' aria-current="page"';
          }
?>>Watch</a></li>
        <li><a href="/sites"
          <?php if ($slug === 'sites') {
              echo ' aria-current="page"';
          }
?>>Directory</a></li>
        <li><a href="/about"
          <?php if ($slug === 'about') {
              echo ' aria-current="page"';
          }
?>>About<span class="u-sr"> BMXfeed</span></a></li>
      </ul>
    </nav>
  </div>
</div>
<!-- literally, no-js -->
</body>
</html>
