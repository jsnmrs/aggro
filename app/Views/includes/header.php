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
  if (env('CI_ENVIRONMENT', 'production') === 'development') {
      echo '[DEV] ';
  }
if (isset($build['site_name'])) {
    echo $build['site_name'] . ' | ';
}
if (isset($build['video_title'])) {
    echo $build['video_title'] . ' | ';
}
if (isset($page) && $page >= 2 && isset($endpage)) {
    echo 'Recent Videos ' . $page . ' of ' . $endpage . ' | ';
}
if (isset($title)) {
    echo esc($title) . ' | ';
}
?>BMXfeed</title>
  <meta charset="utf-8">
  <meta name="description" content="BMXfeed is a bmx news, video aggregator and RSS feed directory">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="google-site-verification" content="3Ljs6uanCn-A0wVw9DzyeXklSNh3ziSq9krzp92AuFM">
  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="manifest" href="/bmxfeed.webmanifest">
  <link rel="alternate" href="/rss" type="application/rss+xml">
  <link rel="alternate" href="/feed" type="application/rss+xml">
  <meta name="color-scheme" content="light">
  <meta name="theme-color" content="#005600">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
<?php if (isset($canonical)): ?>
  <link rel="canonical" href="<?= esc($canonical, 'attr') ?>">
<?php endif; ?>
  <style>
    <?php
  $styles = ROOTPATH . 'public/dist/styles.css';
if (file_exists($styles)) {
    echo file_get_contents($styles, true);
}
?>
  </style>
</head>

<body>
<a href="#content" class="skip">Skip to content</a>

<div class="outer">
  <div class="wrap">
    <header>
      <p class="logo"><a href="/">BMXfeed</a></p>
      <p class="visually-hidden">The BMX news and video aggregator</p>
    </header>
    <nav aria-label="Primary">
      <ul class="nav">
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
        <li><a href="/sites"
          <?php if ($slug === 'sites') {
              echo ' aria-current="page"';
          }
?>>Directory</a></li>
        <li><a href="/about"
          <?php if ($slug === 'about') {
              echo ' aria-current="page"';
          }
?>>About<span class="visually-hidden"> BMXfeed</span></a></li>
      </ul>
    </nav>
  </div>
</div>
