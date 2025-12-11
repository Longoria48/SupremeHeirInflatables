<?php
/* CREATION LOG: 11/19/2025
    From rentals.php: (These are the same across all public pages)
        Error logging
        Required_Once (base path and base url, helper, header, and footer)
            base path and base url
            helper
            DBConfig
            Database
            InventoryItemsDB
            header and footer
    Set page title to contact (gets called in header.php)
    Copied all of main section from cart.html
CREATION LOG: 11/19/2025 */


// Set page title and get the header
$page_title = "Home";
$page_slug = "home";
require_once BASE_PATH.'/src/includes/header.php';
?>
 
 <main>
    <section class="carousel" aria-label="Popular inflatables">
      <div class="carousel-track" id="carouselTrack" data-autoplay="true" data-interval="3500">
        <!-- JS injects items -->
      </div>
      <div class="carousel-enlarge" aria-hidden="true">
        <img alt="">
      </div>
    </section>

    <section class="intro container">
      <h1>Welcome to Supreme Heir Inflatables</h1>
      <p>We provide premium, safety-inspected inflatables fit for royalty — birthdays, school events, block parties, and more.</p>
      <p class="notice">📍 Service area: <strong>State Panhandle</strong> only.</p>
      <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/../index.php?page=rentals">Browse Rentals</a>
    </section>

    <section class="testimonials container">
      <h2>What Our Subjects Say</h2>
      <div class="cards-grid">
        <blockquote class="card">
          <p>“The Royal Bounce Castle was the crowning jewel of our party!”</p>
          <footer>— A. Knight</footer>
        </blockquote>
        <blockquote class="card">
          <p>“Delivery was prompt, setup was swift, and the kids had a blast.”</p>
          <footer>— Q. Archer</footer>
        </blockquote>
        <blockquote class="card">
          <p>“The Majestic Slide lived up to its name. Will rent again!”</p>
          <footer>— D. Duchess</footer>
        </blockquote>
      </div>
    </section>
  </main>
<?php
require_once BASE_PATH . '/src/includes/footer.php';
?>