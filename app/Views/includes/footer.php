<?php

/**
 * @file
 * Site footer include.
 */
?>
<footer>
  <div class="row">
    <div class="four columns" role="contentinfo">
      <p class="tagline">BMXfeed. Since '06.</p>
    </div>
    <div class="eight columns">
      <ul class="u-list-inline nav">
        <li><a href="/"
          <?php if ($slug == "featured") {
            echo " class=\"on\"";
          }
          ?>>News</a></li>
        <li><a href="/stream/"
          <?php if ($slug == "stream") {
            echo " class=\"on\"";
          }
          ?>>Stream</a></li>
        <li><a href="/video/"
          <?php if ($slug == "video") {
            echo " class=\"on\"";
          }
          ?>>Videos</a></li>
        <li><a href="/sites/"
          <?php if ($slug == "directory") {
            echo " class=\"on\"";
          }
          ?>>Directory</a></li>
        <li><a href="/about/"
          <?php if ($slug == "about") {
            echo " class=\"on\"";
          }
          ?>>About<span class="u-hidden-visually"> BMXfeed</span></a></li>
      </ul>
    </div>
  </div>
</footer>

<script src="/js/scripts.js"></script>
</body>
</html>
