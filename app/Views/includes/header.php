<?php

/**
 * @file
 * Site header include.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title><?php
  if ($_ENV['CI_ENVIRONMENT'] == "development") {
    echo "[DEV] ";
  }
  if (isset($build['site_name'])) {
    echo $build['site_name'] . " | ";
  }
  if (isset($build['video_title'])) {
    echo $build['video_title'] . " | ";
  }
  if (isset($title)) {
    echo esc($title) . " | ";
  }
  ?>BMXfeed</title>
  <meta charset="utf-8">
  <meta name="description" content="BMXfeed is a bmx news, video aggregator and RSS feed directory">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="google-site-verification" content="3Ljs6uanCn-A0wVw9DzyeXklSNh3ziSq9krzp92AuFM">
  <meta name="theme-color" content="#005600">
  <link rel="icon" type="image/svg+xml" href="/favicon.svg">
  <link rel="alternate icon" href="/favicon.ico">
  <link rel="manifest" href="/bmxfeed.webmanifest">
  <link rel="alternate" href="/rss/" type="application/rss+xml" title="bmxfeed recent videos">
  <link rel="alternate" href="/feed/" type="application/rss+xml" title="bmxfeed directory updates">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
  <?php if (isset($build['video_title'])) :?>
<meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:site" content="@bmxfeed">
  <meta name="twitter:title" content="<?php echo $build['video_title']; ?>">
  <meta name="twitter:image" content="<?php echo $build['video_thumbnail_url']; ?>">
  <?php endif; ?><style>
    <?php
    $styles = ROOTPATH . "public/dist/styles.css";
    if (file_exists($styles)) {
      echo file_get_contents($styles, TRUE);
    }
    ?>
  </style>
</head>

<body>
<a href="#content" class="skip">Skip to content</a>

<div class="outer">
  <div class="wrap">
    <header>
      <p class="h1"><a href="/" title="BMXfeed home">BMXfeed</a></p>
      <p class="u-sr">The BMX news and video aggregator</p>
    </header>
    <nav aria-label="Main navigation">
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
