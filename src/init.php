<?php 
// init.php -- runs on load establishes system globals, includes, and connects to Databases

require_once __DIR__ . '/../config/config.php'; // get the BASE_PATH and the BASE_URL
require_once BASE_PATH.'/src/includes/helper.php';

//create errorlog
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/logs/app_errors.log');
error_reporting(E_ALL);

// ----------------------------
// Database connection settings
// ----------------------------
$config = require_once BASE_PATH. '/config/DBConfig.php';
require_once BASE_PATH.'/src/models/Database.php';
require_once BASE_PATH.'/src/models/RsvDB.php';
require_once BASE_PATH.'/src/models/InvDB.php';
require_once BASE_PATH.'/src/models/CustDB.php';
require_once BASE_PATH.'/src/models/UsersDB.php';


// ----------------------------
// CONNECT TO DATABASE
// ----------------------------
try {
    $database = new Database($config);
    $pdo = $database->getConnection();
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}



// echo "<script>
//         window.BASE_URL = '" . BASE_URL . "';
//         window.BASE_PATH = '" . BASE_PATH . "';
//       </script>";
//       <script>

?>
<script>
    // Set the app config global variable that can be called by JS
    // This way, JS will know the BASE_URL and BASE_PATH
    // Got this from ChatGPT and don't fully understand it yet.
    window.APP_CONFIG = {
    BASE_URL: <?= json_encode(BASE_URL) ?>,
    BASE_PATH: <?= json_encode(BASE_PATH) ?>
  };
</script>

