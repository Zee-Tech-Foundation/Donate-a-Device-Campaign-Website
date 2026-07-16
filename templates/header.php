<?php
if (!defined('SITE_NAME')) {
    exit('Header could not be loaded.');
}
$pageTitle = $pageTitle ?? SITE_NAME;
$pageDescription = $pageDescription ?? 'Donate devices to learners, educators and communities through Zee Tech Foundation.';
$canonical = $canonical ?? SITE_URL . '/' . ltrim($_SERVER['REQUEST_URI'] ?? '', '/');
$pageRobots = $pageRobots ?? 'index, follow';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title><?= esc($pageTitle); ?></title>
    <meta name="description" content="<?= esc($pageDescription); ?>" />
    <link rel="canonical" href="<?= esc($canonical); ?>" />
    <meta name="robots" content="<?= esc($pageRobots); ?>" />
    <link rel="icon" href="images/favicon.png" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Manrope:wght@400;500;600;700&display=swap"
    />
    <link rel="stylesheet" href="styles.css" />
  </head>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17946932034"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'AW-17946932034');
  </script>
  <body>
    <?php $user = current_user(); ?>
    <header class="site-header">
      <div class="container nav">
        <a class="brand" href="index">
          <img class="brand-logo" src="images/logo.png" width="150px" alt="Zee Tech Foundation logo" />
          <!-- <span class="brand-name">Zee Tech <span>Foundation</span></span> -->
        </a>
        <nav class="nav-links" role="navigation" aria-label="Primary navigation">
          <?php foreach (NAV_ITEMS as $item): ?>
            <?php $active = is_active_nav($item['href']); ?>
            <a href="<?= esc($item['href']); ?>" class="<?= $active ? 'active' : ''; ?>" <?= $active ? 'aria-current="page"' : ''; ?>><?= esc($item['label']); ?></a>
          <?php endforeach; ?>
        </nav>
        <div class="nav-cta">
          <?php if ($user): ?>
            <a class="btn btn-secondary" href="<?= esc($user['role'] === 'admin' ? 'admin-dashboard' : ($user['role'] === 'donor' ? 'donor-dashboard' : 'applicant-dashboard')); ?>">Dashboard</a>
            <a class="btn btn-primary" href="logout">Sign out</a>
          <?php else: ?>
            <a class="btn btn-secondary" href="login">Sign in</a>
            <a class="btn btn-primary" href="register">Register</a>
          <?php endif; ?>
        </div>
        <button class="menu-btn" aria-label="Open menu" aria-expanded="false" aria-controls="mnav">☰</button>
      </div>
      <div id="mnav" class="mobile-nav">
        <?php foreach (NAV_ITEMS as $item): ?>
          <?php $active = is_active_nav($item['href']); ?>
          <a href="<?= esc($item['href']); ?>" <?= $active ? 'aria-current="page"' : ''; ?>><?= esc($item['label']); ?></a>
        <?php endforeach; ?>
        <a href="donate" class="btn btn-primary" style="margin-top:.5rem">Donate a device</a>
        <?php if ($user): ?>
          <a href="<?= esc($user['role'] === 'admin' ? 'admin-dashboard' : ($user['role'] === 'donor' ? 'donor-dashboard' : 'applicant-dashboard')); ?>">Dashboard</a>
          <a href="logout">Sign out</a>
        <?php else: ?>
          <a href="login">Sign in</a>
          <a href="register">Register</a>
        <?php endif; ?>
      </div>
    </header>
    <main>
