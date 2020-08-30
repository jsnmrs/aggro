<?php

/**
 * @file
 * Site footer include.
 */
?>
<div class="outer">
  <div class="wrap">
    <footer>
      <p>BMXfeed. Since '06.</p>
    </footer>
    <nav>
      <ul class="nav">
        <li><a href="/"
          <?php if ($slug == "featured") {
            echo " aria-current=\"page\"";
          }
          ?>>News</a></li>
        <li><a href="/stream/"
          <?php if ($slug == "stream") {
            echo " aria-current=\"page\"";
          }
          ?>>Stream</a></li>
        <li><a href="/video/"
          <?php if ($slug == "video") {
            echo " aria-current=\"page\"";
          }
          ?>>Videos</a></li>
        <li><a href="/sites/"
          <?php if ($slug == "sites") {
            echo " aria-current=\"page\"";
          }
          ?>>Directory</a></li>
        <li><a href="/about/"
          <?php if ($slug == "about") {
            echo " aria-current=\"page\"";
          }
          ?>>About<span class="u-sr"> BMXfeed</span></a></li>
      </ul>
    </nav>
  </div>
</div>

<script src="/js/scripts.js"></script>
</body>
</html>
