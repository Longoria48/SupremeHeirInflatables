<?php
/* UPDATE LOG: 11/19/2025
    From the orderv2 example: (These are the same across all public pages)
        Required_Once
          base path and base url
          helper
          DBConfig
          Database
          InventoryItemsDB (changed name from the database name in orderv2)
          header and footer
    Set page title to "Rentals" (gets called in header.php)
UPDATE LOG: 11/19/2025 */

// Start the session to store messages between requests
session_start();


  $InventoryDB = new InvDB($pdo);

  $errors = [];
  $success = [];


  // GET THE INVENTORY ITEMS AND STORE IN RENTALS
  $RentalsList = $InventoryDB->getAll();

// Set page title and get the header
$page_title = "Rentals";
$page_slug = "rentals";
require_once BASE_PATH.'/src/includes/header.php';
?>

  <main class="container">
    <h1>Rentals</h1>
    <div class="filters">
      <!-- <label>
        Type:
        <select id="filterType">
          <option value="">No filter</option>
          <option value="castle">Bouncy Castle</option>
          <option value="obstacle">Obstacle Course</option>
          <option value="slide">Slide</option>
        </select>
      </label>

      <label>
        Wet/Dry:
        <select id="filterWet">
          <option value="">No filter</option>
          <option value="wet">Wet</option>
          <option value="dry">Dry</option>
        </select>
      </label>

      <label>
        Price:
        <select id="sortPrice">
          <option value="">No sort</option>
          <option value="asc">Low → High</option>
          <option value="desc">High → Low</option>
        </select>
      </label>
    </div>
    -->
    <section class="rentals-grid" id="rentalsGrid" aria-live="polite">
      
  <?php 
    
    $photoFolderLocation = BASE_URL."/assets/images/";
    foreach($RentalsList as $rental) {
        $inventory_id = esc($rental['inventory_id']);
        $name        = esc($rental['product_name']);
        $category    = esc($rental['category']); // e.g. castle/slide/obstacle
        $type        = esc($rental['type']);     // e.g. wet/dry
        $price       = esc($rental['price']);
        $photo       = esc($rental['photo']);

        echo "
          <article class='rental-card'
                   id='item-$inventory_id'
                   data-type='$category'
                   data-wet='$type'
                   data-price='$price'
                   data-name='$name'>
            <div class='rental-media'>
              <img src='$photoFolderLocation$photo' alt='$name photo'>
            </div>
            <div class='rental-info'>
              <h2>$name</h2>
              <p class='price' data-price>$$price / day</p>
              <p class='tags'>Type: $category · $type</p>
              <button class='btn btn-secondary' data-details>Details</button>
            </div>
          </article>
        ";
      }
      
      /* Original vers
      <?php 
        $photoFolderLocation = "../assets/images";
        //echo "<article class='rental-card' style='color:red'>Test Rentals Card</article>";
        // load in the rentals with a foreach!
        foreach($RentalsList as $rental) {
            $inventory_id = esc($rental['inventory_id']);
            $name = esc($rental['product_name']);
            $category = esc($rental['category']);
            $type = esc($rental['type']);
            $price = esc($rental['price']);
            $photo = esc($rental['photo']);
            echo "
                <article class='rental-card' data-type='$category' data-wet='$type' data-price='$price' data-name='name'>
                <div class='rental-media'>
                    <img src='$photoFolderLocation$photo' alt='$name photo'>
                </div>
                <div class='rental-info'>
                    <h2>$name</h2>
                    <p class='price' data-price>$$price / day</p>
                    <p class='tags'>Type: $category . $type</p>
                    <button class='btn btn-secondary' data-details>Details</button>
                </div>
                
                </article>
            ";
        } */

      
        // Original test
        /* foreach($RentalsList as $rental) {
            $inventory_id = esc($rental['inventory_id']);
            $name = esc($rental['inventory_id']);
            $price = esc($rental['price']);
            echo "<article>$inventory_id - $name - $price</article>";
        } */
      ?>
    </section>
  </main>

<?php
require_once BASE_PATH . '/src/includes/footer.php';
?>