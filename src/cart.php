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
/* TO DO: 
    This will need other databases (to accomplish reservation and customer creation)
    Idk where the 'create order' buttons went
*/ 

// Set page title and get the header
$page_title = "Cart";
$page_slug = "cart";


#region Added

session_start();

require_once BASE_PATH.'/src/includes/header.php';
require_once __DIR__ . '/../src/init.php';

$flash_error = '';
$flash_success = '';

foreach (['customer_error','inventory_error','search_error','update_error'] as $key) {
    if (isset($_SESSION[$key])) {
        $flash_error .= $_SESSION[$key] . "<br>";
        unset($_SESSION[$key]);
    }
}

foreach (['customer_success','inventory_success','search_success','update_success'] as $key) {
    if (isset($_SESSION[$key])) {
        $flash_success .= $_SESSION[$key] . "<br>";
        unset($_SESSION[$key]);
    }
}
try 
{
    $database = new Database($config);
    $pdo = $database->getConnection();
}
catch (Exception $e)
{
    error_log("Database connection failed: ". $e->getMessage());
    die("Database connection failed. Please try again later.");
}

$RsvDB = new RsvDB($pdo);
$CustDB = new CustDB($pdo);
$InvDB = new InvDB($pdo);


$firstName = $lastName = $phone = $email = $address = $city = $zip = $quantity = $item = "";
$startDate = $endDate = "";

// Retrieve sticky form values from session if present
$firstName = $_SESSION['form_firstName'] ?? $firstName;
$lastName = $_SESSION['form_lastName'] ?? $lastName;
$phone = $_SESSION['form_phone'] ?? $phone;
$email = $_SESSION['form_email'] ?? $email;
$address = $_SESSION['form_address'] ?? $address;
$city = $_SESSION['form_city'] ?? $city;
$zip = $_SESSION['form_zip'] ?? $zip;
$item = $_SESSION['form_item'] ?? $item;
$startDate = $_SESSION['form_startDate'] ?? $startDate;
$endDate = $_SESSION['form_endDate'] ?? $endDate;
$quantity = $_SESSION['form_quantity'] ?? $quantity;

// Clear sticky form values after retrieving them
unset($_SESSION['form_firstName'], $_SESSION['form_lastName'], $_SESSION['form_phone'], $_SESSION['form_email'], 
      $_SESSION['form_address'], $_SESSION['form_city'], $_SESSION['form_zip'], $_SESSION['form_item'], 
      $_SESSION['form_startDate'], $_SESSION['form_endDate'], $_SESSION['form_quantity']);



#endregion Added



//---------------------------------------------------------------------------------	
//								Validate Info on Continue
//---------------------------------------------------------------------------------	

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['continue']))
{
  echo "<script>console.log('Continue clicked')</script>";

    $errors = []; 
    $success = "";
    $firstName = clean('firstName');
    $lastName = clean('lastName'); 
    $phone = clean('phone');
    $email = clean('email', 'email');
    $address = clean('address');
    $city = clean('city'); 
    $zip = clean('zip');
    $item = clean('item');
    $startDate = clean('startDate', 'date');
    $endDate = clean('endDate', 'date');
    $quantity = clean('quantity', 'int'); 



    $cartItems = json_decode($_POST['cart_json'] ?? '[]', true);
//---------------------------------------------------------------------------------	
//									User Validation
//---------------------------------------------------------------------------------	
    if(empty($firstName))
    {
        $errors[] =  "First name is required.";
    }
	if(preg_match('/\d/', $firstName))
	{
		$errors[] = "First name cannot contain numbers.";
	}
	
    if(empty($lastName))
    {
        $errors[] =  "Last name is required.";
    }
	if(preg_match('/\d/', $lastName))
	{
		$errors[] = "Last name cannot contain numbers.";
	}
  //// Phone Validation
  if (empty($phone))
	{
    $errors[] = "Phone number is required.";
  }
	else 
	{
    // Remove anything that isn't a digit
    $digits = preg_replace('/\D/', '', $phone);

    // Check if it has exactly 10 digits
    if (strlen($digits) !== 10)
		{
      $errors[] = "Phone number must be a 10-digit U.S. number.";
    }
		else
		{
      // Format it as ###-###-####
        $phone = substr($digits, 0, 3) . '-' . substr($digits, 3, 3) . '-' . substr($digits, 6, 4);
    }
  }

    if (empty($email))
	{
        $errors[] = "Email is required";
    }
	else if(!filter_var($email, FILTER_VALIDATE_EMAIL))
	{
        $errors[] = "Invalid email format";
    }

//---------------------------------------------------------------------------------	
//									Address Validation
//---------------------------------------------------------------------------------	
    if(empty($address))
    {
      $errors[] =  "Address is required.";
    }
    if(empty($city))
    {
      $errors[] =  "City is required.";
    }
    else if(preg_match('/\d/', $city))
    {
      $errors[] = "City cannot contain numbers.";
    }
      if (empty($zip)) {
        $errors[] = "Zip code is required.";
      }
    else if(!preg_match('/^\d{5}$/', $zip))
    {
      $errors[] = "Zip code can only contain 5 digits.";
    }	

    foreach ($errors as $error) 
    {
        echo "<script>console.log('Error: " . addslashes($error) . "');</script>";
    }

//---------------------------------------------------------------------------------	
//									Date Validation
//---------------------------------------------------------------------------------	
  //Fix our timezone issue.
  date_default_timezone_set('America/Chicago');
	$start = $startDate ? DateTimeImmutable::createFromFormat('Y-m-d', $startDate) : false;
	$end = $endDate ? DateTimeImmutable::createFromFormat('Y-m-d', $endDate) : false;
	$today = new DateTimeImmutable('today');
	
    if (empty($startDate)) 
	{
        $errors[] = "Start date is required.";
    }
	else if($startDate && !$start)
	{
		$errors[] = "Start date format is invalid";
	}

    if (empty($endDate)) 
	{
        $errors[] = "End date is required.";
    } 
	else if($endDate && !$end)
	{
		$errors[] = "End date format is invalid";
	}	
	//Only run business rules if both dates parsed correctly
	if($start && $end)
	{
		//Days from TODAY to start
		$diffFromToday = (int)$today->diff($start)->format('%r%a');
		
		//Rental length: days between start & end
		$spanDays = (int)$start->diff($end)->format('%r%a');
		
		//Start must be after TODAY
		if($diffFromToday<=0)
		{
			$errors[] = "Start date must be at least 1 day after today.";
		}
		
		//End must be at least next day
		if($spanDays <= 0)
		{
			$errors[] = "End date must be at least 1 day after start date.";
		}
		
		//Max of a 3 day Rental
		if($spanDays > 3)
		{
			$errors[] = "Rental length cannot exceed 3 consecutive days due to equipment maintenance.";
		}
	}



#region Added2


if (empty($errors))
    {
        try
        {
          //To avoid half transactions if failed
          $RsvDB->beginTransaction();

          //Verify customer exists
          $customer = $CustDB->findCustomer($firstName, $lastName, $phone, $email);

          //If no customer then create one
          if(!$customer)
          {
            $customerId = $CustDB->insertCustomer($firstName, $lastName, $phone, $email);
          }
          else
          {
            $customerId = (int)$customer['customer_id'];
          }

          //Look up the inventory item by product_name
          //Lock inventory row for this item so stock & availability
          // cannot change under us while we compute availability
          // $inventory = $InvDB->inventoryLock($item);

          // //If no item found throw exception
          // if(!$inventory)
          // {
          //   throw new Exception("Selected item not found in inventory.");
          // }

          // $inventoryId = (int)$inventory['inventory_id'];
          // $stock = (int)$inventory['stock'];

          // // will need to check if quantity is greater than the database stock 
          // if($quantity > $stock)
          // {
          //   throw new Exception("$item only has ($stock) in stock.");
          // }

          // //Check how many units are already reserved for overlapping dates
          // $reservedQty = $InvDB->inventoryOverlap($inventoryId, $startDate, $endDate);

          // //Check availability against reserved qty
          // $available = $stock - $reservedQty;

          // //If out of stock throw exception
          // if($available < $quantity)
          // {
          //   throw new Exception($inventory['product_name']." only has ($available) available for the selected dates.");
          // }
          
          //Insert reservation
          $reservationId = $RsvDB->createRSV($customerId,$startDate,$endDate,$address,$city,$zip);
          $successItems = []; //array to hold each item and quantity



#region Added 3


// Loop through all cart items and add them to rsv_details
foreach ($cartItems as $ci) {
    $itemName  = $ci['name'];
    $qty       = (int)$ci['quantity'];

    // Lock inventory row for this item
    $inventory = $InvDB->inventoryLock($itemName);
    if (!$inventory) {
        throw new Exception("Item '$itemName' not found in inventory.");
    }

    $inventoryId = (int)$inventory['inventory_id'];
    $stock       = (int)$inventory['stock'];

    // Check availability
    $reservedQty = $InvDB->inventoryOverlap($inventoryId, $startDate, $endDate);
    $available   = $stock - $reservedQty;

    if ($available < $qty) {
        throw new Exception("{$inventory['product_name']} only has ($available) available for the selected dates.");
    }

    // Insert into rsv_details
    $RsvDB->updateRsvDetails($reservationId, $inventoryId, $qty);


        // Add to success message array
    $successItems[] = esc($inventory['product_name']) . " x $qty";
}

// Commit transaction after all items processed
$RsvDB->commit();

#endregion Added 3









         // $RsvDB->updateRsvDetails($reservationId, $inventoryId, (int)$quantity);
        //  $RsvDB->commit();

          
           // Build final success message
          $success = "Reservation created! #$reservationId • " . implode(" • ", $successItems) .
           " • Dates: " . esc($startDate) . " to " . esc($endDate);


          $_SESSION['customer_success'] = $success;
          $_SESSION['clear_cart'] = true;

          // Reset form fields
          $firstName = $lastName = $phone = $email = $address = $city = $zip = "";
          $startDate = $endDate = $item = $quantity = "";

          // Clear the cart after successful reservation
          unset($_SESSION['cart']);

          header("Location: " . BASE_URL . "/index.php?page=cart");
          exit;

        }
        catch(PDOException $e)
        {
            if($RsvDB->inTransaction())
            {
                $RsvDB->rollBack();
            }
            error_log("Database error in create reservation: " . esc($e->getMessage()));
            $_SESSION['customer_error'] = "Error creating reservation. Please try again later.";
            header("Location: " . BASE_URL . "/index.php?page=cart");
            exit;
        }
        catch(Exception $e)
        {
            if ($RsvDB->inTransaction()) {
                $RsvDB->rollBack();
            }

            error_log("General error in create reservation: " . $e->getMessage());
            $_SESSION['customer_error'] = "Error creating reservation. Please try again later.";
            header("Location: " . BASE_URL . "/index.php?page=cart");
            exit;
        }
  }
    else
    {
        $_SESSION['customer_error'] = implode("<br>", $errors); //implode joins array elements into a string. Used for display
        // Store form values in session to make form sticky
        $_SESSION['form_firstName'] = $firstName;
        $_SESSION['form_lastName'] = $lastName;
        $_SESSION['form_phone'] = $phone;
        $_SESSION['form_email'] = $email;
        $_SESSION['form_address'] = $address;
        $_SESSION['form_city'] = $city;
        $_SESSION['form_zip'] = $zip;
        $_SESSION['form_item'] = $item;
        $_SESSION['form_startDate'] = $startDate;
        $_SESSION['form_endDate'] = $endDate;
        $_SESSION['form_quantity'] = $quantity;
        header("Location: " . BASE_URL . "/index.php?page=cart");
        exit;
    }


  }

#endregion Added2




  #region Quantity Test
// $conn = new CustDB($pdo);
//     if (empty($errors)) {
//         try {
//             $conn->beginTransaction();

//             // ---------------------------
//             // 1. CUSTOMER LOOKUP / CREATE
//             // ---------------------------
//             $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE phone = ?");
//             $stmt->execute([$phone]);
//             $customer = $stmt->fetch(PDO::FETCH_ASSOC);

//             if (!$customer) {
//                 $insertCustomer = $conn->prepare(
//                     "INSERT INTO customers(first_name, last_name, phone, email) VALUES(?,?,?,?)"
//                 );
//                 $insertCustomer->execute([$firstName, $lastName, $phone, $email]);
//                 $customerId = (int)$conn->lastInsertId();
//             } else {
//                 $customerId = (int)$customer['customer_id'];
//             }

//             // ----------------------------
//             // 2. CREATE MAIN RESERVATION
//             // ----------------------------
//             $insertRsv = $conn->prepare("
//                 INSERT INTO reservations (customer_id, start_date, end_date, order_status, address, city, zip)
//                 VALUES (:customer_id, :start_date, :end_date, :status, :address, :city, :zip)
//             ");

//             $insertRsv->execute([
//                 ':customer_id' => $customerId,
//                 ':start_date'  => $startDate,
//                 ':end_date'    => $endDate,
//                 ':status'      => 'Pending',
//                 ':address'     => $address,
//                 ':city'        => $city,
//                 ':zip'         => $zip
//             ]);

//             $reservationId = (int)$conn->lastInsertId();

//             // --------------------------------------
//             // 3. VALIDATE + INSERT EACH CART ITEM
//             // --------------------------------------
//             foreach ($cartItems as $ci) {

//                 $itemName  = $ci['name'];
//                 $quantity  = (int)$ci['quantity'];

//                 // get inventory row
//                 $inventoryStmt = $conn->prepare("
//                     SELECT inventory_id, stock, product_name
//                     FROM inventory
//                     WHERE product_name = ?
//                     LIMIT 1
//                     FOR UPDATE
//                 ");
//                 $inventoryStmt->execute([$itemName]);
//                 $inventory = $inventoryStmt->fetch(PDO::FETCH_ASSOC);

//                 if (!$inventory) {
//                     throw new Exception("Item '$itemName' not found in inventory.");
//                 }

//                 $inventoryId = (int)$inventory['inventory_id'];
//                 $stock       = (int)$inventory['stock'];

//                 // calculate reserved amount for date range
//                 $overLapStmt = $conn->prepare("
//                     SELECT COALESCE(SUM(d.quantity), 0) AS reserved_qty
//                     FROM rsv_details d
//                     JOIN reservations r ON r.reservation_id = d.reservation_id
//                     WHERE d.inventory_id = :inv_id
//                     AND NOT (r.end_date < :start_date OR r.start_date > :end_date)
//                     AND r.order_status <> 'Canceled'
//                     FOR UPDATE
//                 ");

//                 $overLapStmt->execute([
//                     ':inv_id'     => $inventoryId,
//                     ':start_date' => $startDate,
//                     ':end_date'   => $endDate
//                 ]);

//                 $reservedQty = (int)$overLapStmt->fetchColumn();

//                 // availability check
//                 $available = $stock - $reservedQty;

//                 if ($available < $quantity) {
//                     $errors[] = "{$inventory['product_name']} has only ($available) available for the selected dates.";
//                     throw new Exception("{$inventory['product_name']} has only ($available) available for the selected dates.");
//                 }

//                 // insert into rsv_details
//                 $insertRsvDetail = $conn->prepare("
//                     INSERT INTO rsv_details (reservation_id, inventory_id, quantity)
//                     VALUES (?, ?, ?)
//                 ");

//                 $insertRsvDetail->execute([$reservationId, $inventoryId, $quantity]);
//             }

//             // ---------------------------
//             // SUCCESS
//             // ---------------------------
//             $conn->commit();

//             $_SESSION['customer_success'] = "Reservation #$reservationId created successfully!";

//             header("Location: " . $_SERVER['PHP_SELF']);
//             exit;

//         } catch (Exception $e) {
//             if ($conn->inTransaction()) {
//                 $conn->rollBack();
//             }
//             $_SESSION['customer_error'] = $e->getMessage();
//         }
//     }
//     if (!empty($errors)) {
//         $_SESSION['customer_error'] = implode("<br>", $errors);

//         // Store sticky form values
//         $_SESSION['form_firstName'] = $firstName;
//         $_SESSION['form_lastName']  = $lastName;
//         $_SESSION['form_phone']     = $phone;
//         $_SESSION['form_email']     = $email;
//         $_SESSION['form_address']   = $address;
//         $_SESSION['form_city']      = $city;
//         $_SESSION['form_zip']       = $zip;

//         header("Location: index.php?page=cart");
//         exit;
//     }
// }

#endregion

?>

  <?php if ($flash_error): ?>
    <p class="errorMsg" style="color:white; padding:20px;"><?= $flash_error ?></p>
  <?php endif; ?>

  <?php if ($flash_success): ?>
    <p class="errorMsg" style="color:white; padding:20px;"><?= $flash_success ?></p>
  <?php endif; ?>



<?php if (!empty($_SESSION['clear_cart'])): ?>
<script>
    console.log("Clearing LocalStorage cart...");
    setCart({ items: [], personal: {} });
    render();
</script>
<?php 
    unset($_SESSION['clear_cart']); 
endif; 
?>





<main class="container cart-layout">
    <section class="cart-list" id="cartList" aria-label="Cart items">
      <div id="cartDates" class="cart-dates" aria-label="Reservation dates" style="display:none;">
        <h2 class="purpleColor">Reservation Dates</h2>
        <form id="cartDatesForm" class="res-form" novalidate>
          <div class="grid">
            <label>Start Date<input name="startDate" id="cartStartDate" type="date" required></label>
            <label>End Date<input name="endDate" id="cartEndDate" type="date" required></label>
          </div>
          <p id="cartDatesPreview" class="muted" style="margin-top:.5rem;">Selected: —</p>
        </form>
      </div>
      <div id="cartItems"></div>
    </section>

    <aside class="cart-summary" aria-label="Order summary">
      <h2 class = "whiteColor">Reservation Summary</h2>
      <div class="summary-money">
        <div class="line"><span>Items total (tax included)</span><strong id="grandTotal">$0</strong></div>
      </div>
      <p class="whiteColor">You'll enter your personal info at the start of checkout.</p>
      <div class="actions">
        <button class="btn btn-primary" type="button" id="btnCheckoutCart">Check Out</button>
      </div>
    </aside>
</main>

<?php
require_once BASE_PATH . '/src/includes/footer.php';
?>
