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
  <link rel="manifest" href="/manifest.json">
  <link rel="alternate" href="/rss/" type="application/rss+xml" title="bmxfeed recent videos">
  <link rel="alternate" href="/feed/" type="application/rss+xml" title="bmxfeed directory updates">
  <style type="text/css">
    <?php
    $file = file_get_contents(ROOTPATH . "public/css/styles.css", TRUE);
    echo $file;
    ?>
  </style>
</head>

<body>
<a href="#content" class="skip">Skip to content</a>

<header>
  <div class="row">
    <div class="four columns" role="banner">
      <p class="h1"><a href="/" title="BMXfeed home">BMXfeed</a></p>
      <p class="u-hidden-visually">The BMX news and video aggregator</p>
    </div>
    <div class="eight columns" role="navigation">
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
          <?php if ($slug == "sites") {
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
</header>
