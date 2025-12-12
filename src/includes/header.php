<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Other version said 'Home' if no page title existed. Mine says the company name -->
     <!-- Page title here is snake case, not camel case -->
    <title><?php echo isset($page_title) ? htmlspecialchars('Supreme Heir Inflatables — '.$page_title) : 'Supreme Heir Inflatables'; ?></title>
  <!--  <link rel="stylesheet" href="<(?)php echo BASE_URL; ?>/assets/css/styles.css">    (added () around ? to comment whole line -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/styles.css">

  <!--  <script defer src="<(?)php echo BASE_URL; ?>/assets/scripts/app.js"></script>  -->
    <script defer src="<?php echo BASE_URL; ?>/public/assets/scripts/app.js"></script>

    <!-- Alternate version included this script: (commented php so it doesn't run here) -->
    <script>window.BASE_URL = '<?php echo BASE_URL; ?>';</script>
    
</head>
<!-- Alternate version called the $page_slug variable just $page -->
<body data-page="<?= isset($page_slug) ? htmlspecialchars($page_slug) : 'home'?>">
  <header class="site-header">
    <div class="brand">
      <a href="<?php echo BASE_URL; ?>/index.php?page=home" class="logo">👑 Supreme Heir Inflatables</a>
      <p class="tagline">Whimsical. Safe. Royal-grade fun.</p>
    </div>

    <nav class="site-nav">
      <button class="nav-toggle" aria-expanded="false" aria-controls="navMenu">☰</button>
      <ul id="navMenu">
            <!-- li tags included in same line as link/anchor tags -->

            <!-- Added the active style (page you are on is always highlighted purple in the nav) via checking the $page_slug -->
            <li><a href="<?php echo BASE_URL; ?>/index.php?page=home" class="<?php echo (isset($page_slug) && $page_slug === 'home') ? 'active' : ''; ?>">Home</a></li>
            <li><a href="<?php echo BASE_URL; ?>/index.php?page=rentals" class="<?php echo (isset($page_slug) && $page_slug === 'rentals') ? 'active' : ''; ?>">Rentals</a></li>
            <li><a href="<?php echo BASE_URL; ?>/index.php?page=cart" class="<?php echo (isset($page_slug) && $page_slug === 'cart') ? 'active' : ''; ?>">Cart</a></li>
            <li><a href="<?php echo BASE_URL; ?>/index.php?page=contact" class="<?php echo (isset($page_slug) && $page_slug === 'contact') ? 'active' : ''; ?>">Contact</a></li>
        </ul>
    </nav>
    </header>