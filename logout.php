
//logout.php
//require_once __DIR__ . '/src/init.php';
///session_unset();              // Clear session variables
//session_destroy();            // Destroy session data

//setcookie(session_name(), '', time() - 3600, '/');   // Delete the browser session cookie
//by setting expiration time in the past 

//header("Location: " . BASE_URL . "/index.php?page=login");

//header("Location: /supremeheirinflatables/public/login.php");
//exit;



<?php

$page = 'logout';
// logout.php
session_start();                // Start the session first
session_unset();                // Clear all session variables
session_destroy();              // Destroy the session

setcookie(session_name(), '', time() - 3600, '/');   // Delete the session cookie
http_response_code(302);
header("Location: index.php?page=login");
exit;
?>
