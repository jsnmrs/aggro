<?php

/**
 * @file
 * Site header include.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
  <style>
      <?php
    $styles = ROOTPATH . 'public/dist/styles.css';
if (file_exists($styles)) {
    echo file_get_contents($styles, true);
}
?>
  </style>
  <meta name="description" content="BMXfeed is a bmx news, video aggregator and RSS feed directory">
  <meta name="google-site-verification" content="3Ljs6uanCn-A0wVw9DzyeXklSNh3ziSq9krzp92AuFM">
  <link rel="icon" href="<?= base_url('favicon.ico') ?>" sizes="any">
  <link rel="icon" href="<?= base_url('favicon.svg') ?>" type="image/svg+xml">
  <link rel="manifest" href="<?= base_url('bmxfeed.webmanifest') ?>?v=<?= filemtime(FCPATH . 'bmxfeed.webmanifest') ?>">
  <link rel="alternate" href="<?= base_url('rss') ?>" type="application/rss+xml">
  <link rel="alternate" href="<?= base_url('feed') ?>" type="application/rss+xml">
  <meta name="color-scheme" content="light">
  <meta name="theme-color" content="#005600">
  <link rel="apple-touch-icon" href="<?= base_url('apple-touch-icon.png') ?>">
<?php if (isset($canonical)): ?>
  <link rel="canonical" href="<?= esc($canonical) ?>">
<?php endif; ?>
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
