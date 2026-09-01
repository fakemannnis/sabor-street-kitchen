<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$base      = $base ?? '';
$pageTitle = $pageTitle ?? 'Sabor Street Kitchen';
$pageDesc  = $pageDesc ?? 'Sabor Street Kitchen — night-market tacos, elote, and aguas frescas in Merewether NSW. Order online for pickup.';
$activeNav = $activeNav ?? '';
$canonical = $canonical ?? '';
$user      = current_user();
$flashes   = get_flashes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= h($pageTitle) ?> | Sabor Street Kitchen</title>
  <meta name="description" content="<?= h($pageDesc) ?>" />
  <meta name="robots" content="index, follow" />
  <?php if ($canonical): ?>
  <link rel="canonical" href="https://www.saborstreetkitchen.example/<?= h($canonical) ?>" />
  <?php endif; ?>
  <meta property="og:title" content="<?= h($pageTitle) ?> | Sabor Street Kitchen" />
  <meta property="og:description" content="<?= h($pageDesc) ?>" />
  <meta property="og:type" content="website" />
  <meta name="twitter:card" content="summary" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Space+Mono:wght@400;700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="icon" href="<?= h($base) ?>images/logo-mark.svg" type="image/svg+xml" />
  <link rel="stylesheet" href="<?= h($base) ?>css/styles.css" />
  <link rel="stylesheet" href="<?= h($base) ?>css/responsive.css" />
</head>
<body>
  <a class="skip-link" href="#main">Skip to main content</a>

  <header class="site-header">
    <div class="container nav-bar">
      <a class="brand" href="<?= h($base) ?>index.php">
        <img src="<?= h($base) ?>images/logo-mark.svg" alt="" width="42" height="42" />
        <span class="brand-text">Sabor<span>Street Kitchen</span></span>
      </a>

      <nav class="main-nav" id="primary-navigation" aria-label="Primary">
        <ul>
          <li><a href="<?= h($base) ?>index.php" <?= $activeNav === 'home' ? 'aria-current="page"' : '' ?>>Home</a></li>
          <li><a href="<?= h($base) ?>menu.php" <?= $activeNav === 'menu' ? 'aria-current="page"' : '' ?>>Menu &amp; Order</a></li>
          <li><a href="<?= h($base) ?>about.php" <?= $activeNav === 'about' ? 'aria-current="page"' : '' ?>>Our Story</a></li>
          <li><a href="<?= h($base) ?>gallery.php" <?= $activeNav === 'gallery' ? 'aria-current="page"' : '' ?>>Gallery</a></li>
          <li><a href="<?= h($base) ?>contact.php" <?= $activeNav === 'contact' ? 'aria-current="page"' : '' ?>>Contact</a></li>
          <?php if ($user): ?>
            <?php if ($user['role'] === 'admin'): ?>
              <li><a href="<?= h($base) ?>admin/index.php" <?= $activeNav === 'admin' ? 'aria-current="page"' : '' ?>>Admin</a></li>
            <?php else: ?>
              <li><a href="<?= h($base) ?>account.php" <?= $activeNav === 'account' ? 'aria-current="page"' : '' ?>>My Account</a></li>
            <?php endif; ?>
            <li><a href="<?= h($base) ?>logout.php">Log Out</a></li>
          <?php else: ?>
            <li><a href="<?= h($base) ?>login.php" <?= $activeNav === 'login' ? 'aria-current="page"' : '' ?>>Log In</a></li>
            <li><a href="<?= h($base) ?>register.php" <?= $activeNav === 'register' ? 'aria-current="page"' : '' ?>>Register</a></li>
          <?php endif; ?>
        </ul>
      </nav>

      <div style="display:flex; align-items:center; gap:0.75rem;">
        <a class="nav-cart" href="<?= h($base) ?>menu.php#order-summary" aria-label="View order, 0 items">
          <span aria-hidden="true">&#128722;</span> Order <span class="count" data-cart-count>0</span>
        </a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" aria-label="Toggle menu">
          <span class="bar"></span><span class="bar"></span><span class="bar"></span>
        </button>
      </div>
    </div>
  </header>

  <?php if ($flashes): ?>
    <div class="container" style="padding-top:1rem;">
      <?php foreach ($flashes as $f): ?>
        <div class="form-feedback is-visible <?= $f['type'] === 'error' ? 'error' : 'success' ?>" role="alert" style="margin-bottom:0.75rem;">
          <?= h($f['message']) ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <main id="main">
