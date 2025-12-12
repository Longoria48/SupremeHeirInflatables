<?php
session_start(); 
require_once __DIR__ . '/../src/init.php';

//login.php
$usersDB = new UsersDB($pdo);
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';   // DO NOT sanitize password

    $user = $usersDB->getUserbyUsername($username);

    // Main verification line
    if ($user && password_verify($password, $user['password'])) {
		session_regenerate_id(true); //secures against session hijaking
        $_SESSION['is_admin'] = true;
        $_SESSION['username'] = $username;
       header("Location: " . BASE_URL . "/../index.php?page=admin");
       //header("Location: /supremeheirinflatables/index.php?page=admin");
       
        exit;
    } else {
        $message = "*Invalid login.*";
    }
}

// Set page title and get the header
$page_title = "Login";
$page_slug = "login";
require_once BASE_PATH.'/src/includes/adminheader.php';
?>

  <main class="container">

    <section class="login-card">
      <h1>Sign in</h1>
      <p class="muted">This page is hidden from customers; access via direct URL only.</p>
      <form id="loginForm" class="admin-form" method="POST" action="">
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

      <?php if (!empty($message)): ?>
        <p class="login-error" style="color:red;font-weight:bold; margin-bottom:1rem;">
        <?= htmlspecialchars($message) ?>
        </p>
      <?php endif; ?>

      <!-- <p class="muted" style="margin-top:.75rem">For now, pressing “Log in” with empty fields opens the Admin panel.</p> -->
    </section>
  </main>

<?php
    require_once BASE_PATH. '/src/includes/footer.php';
?>
