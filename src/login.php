<?php
// require_once __DIR__ . '/../src/init.php';


// Set page title and get the header
$page_title = "Login";
$page_slug = "login";
require_once BASE_PATH.'/src/includes/adminheader.php';
?>

  <main class="container">

    <section class="login-card">
      <h1>Sign in</h1>
      <p class="muted">This page is hidden from customers; access via direct URL only.</p>
      <form id="loginForm" class="admin-form">
        <div class="grid">
          <label>Username
            <input name="username" autocomplete="username" placeholder="owner">
          </label>
          <label>Password
            <input name="password" type="password" autocomplete="current-password" placeholder="••••••••">
          </label>
        </div>
        <div class="admin-actions">
          <button type="submit" class="btn btn-primary">Log in</button>
          <button type="reset" class="btn btn-secondary">Clear</button>
        </div>
      </form>
      <!-- <p class="muted" style="margin-top:.75rem">For now, pressing “Log in” with empty fields opens the Admin panel.</p> -->
    </section>
  </main>

<?php
    require_once BASE_PATH. '/src/includes/footer.php';
?>
