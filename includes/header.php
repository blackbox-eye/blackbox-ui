<?php
// public_html/includes/header.php
session_start();

// db.php ligger i public_html/ – vi går én mappe op fra includes/
require __DIR__ . '/../db.php';

// (På beskyttede sider kan du tjekke login her eller inde i selve siden)
?>
<!DOCTYPE html>
<html lang="da">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>
    <?= isset($page_title)
      ? htmlspecialchars($page_title) . ' – blackbox.codes'
      : 'blackbox.codes' ?>
  </title>

  <!-- Self-hosted fonts for performance -->
  <link rel="stylesheet" href="/assets/fonts/fonts.css">

  <!-- Compiled Tailwind CSS (local, eliminates CDN/Rocket Loader issues) -->
  <link rel="stylesheet" href="/assets/css/tailwind.full.css">

  <!-- Hoved-stylesheet -->
  <link rel="stylesheet" href="/style.css">
</head>

<body>
