<?php
// public_html/includes/header.php
session_start();

// db.php ligger i public_html/ – vi går én mappe op fra includes/
require __DIR__ . '/../db.php';
require __DIR__ . '/i18n.php';

$current_language = bbx_get_language();

// (På beskyttede sider kan du tjekke login her eller inde i selve siden)
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_language) ?>" data-lang="<?= htmlspecialchars($current_language) ?>">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>
    <?= isset($page_title)
      ? htmlspecialchars($page_title) . ' – blackbox.codes'
      : 'blackbox.codes' ?>
  </title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_black_32x32.png">
  <link rel="icon" type="image/png" sizes="192x192" href="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_black_256x256.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_black_256x256.png">
  <link rel="shortcut icon" href="/assets/logo%20pakker%20BlackboxEYE/blackboxeye_logo_package_full/BlackboxEYE_black.ico">

  <!-- Google Font -->
  <link
    href="https://fonts.googleapis.com/css2?family=Source+Code+Pro&display=swap"
    rel="stylesheet"
    crossorigin="anonymous">

  <!-- Compiled Tailwind CSS (local, eliminates CDN/Rocket Loader issues) -->
  <link rel="stylesheet" href="/assets/css/tailwind.full.css">

  <!-- Hoved-stylesheet -->
  <link rel="stylesheet" href="/style.css">
  <link rel="stylesheet" href="/assets/css/theme-overrides.css">
</head>

<body data-theme="dark">
