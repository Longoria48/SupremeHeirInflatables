<?php
/* CREATION LOG: 11/19/2025
    From rentals.php: (These are the same across all public pages)
        Error logging
        Required_Once
          base path and base url
          helper
          header and footer
    Set page title to "Contact" (gets called in header.php)
    Copied all of main section from contact.html
CREATION LOG: 11/19/2025 */

$page_title = "Contact";
$page_slug = "contact";
require_once BASE_PATH.'/src/includes/header.php';
?>

  <main class="container">
    <h1>Contact Us</h1>
    <p>We’re happy to help with reservations, changes, and event questions.</p>
    <div class="contact-box">
      <p>📞 Phone: <a href="tel:+15555550100">(555) 555-0100</a></p>
      <p>✉️ Email: <a href="mailto:hello@supremeheir.example">hello@supremeheir.example</a></p>
      <p>🏰 Address: 100 Crown Lane, Capital City, State Panhandle</p>
      <p class="muted">Note: Payment is in person on delivery day, full amount up front.</p>
    </div>
  </main>

<?php
require_once BASE_PATH . '/src/includes/footer.php';
?>