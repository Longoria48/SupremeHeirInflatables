<?php
require_once __DIR__ . '/../src/init.php';

#region CRUD
$page = 'admin';

session_start();

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
#endregion Database Connection

#region Create RSV
$RsvDB = new RsvDB($pdo);
$CustDB = new CustDB($pdo);
$InvDB = new InvDB($pdo);

//Variables
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

//---------------------------------------------------------------------------------	
//								START OF CREATE RSV
//---------------------------------------------------------------------------------	

//Runs if user presses submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) 
{
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

//---------------------------
//    User Validation
//---------------------------
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

//---------------------------
//    Address Validation
//---------------------------	

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
	
//---------------------------
//    Date Validation
//---------------------------
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
	
//---------------------------
// Item/Quantity Validation
//---------------------------

    if (empty($item)) 
	{
        $errors[] = "Item is required.";
    } 	
    //Allows for only whole numbers (cant have 1/2 a jump house)
	if ($quantity === '' || $quantity === null)
	{
		$errors[] = "Quantity is required.";
	}
	else if(filter_var($quantity, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) === false)
	{
		$errors[] = "Quantity must be a whole number of at least 1.";
	}

    if (empty($errors))
    {
        try
        {
          //To avoid half transactions if failed
          $RsvDB->beginTransaction();

          //Verify customer exists
          $customer = $CustDB->findCustomer($firstName, $lastName, $email, $phone);

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
          $inventory = $InvDB->inventoryLock($item);

          //If no item found throw exception
          if(!$inventory)
          {
            throw new Exception("Selected item not found in inventory.");
          }

          $inventoryId = (int)$inventory['inventory_id'];
          $stock = (int)$inventory['stock'];

          // will need to check if quantity is greater than the database stock 
          if($quantity > $stock)
          {
            throw new Exception("$item only has ($stock) in stock.");
          }

          //Check how many units are already reserved for overlapping dates
          $reservedQty = $InvDB->inventoryOverlap($inventoryId, $startDate, $endDate);

          //Check availability against reserved qty
          $available = $stock - $reservedQty;

          //If out of stock throw exception
          if($available < $quantity)
          {
            throw new Exception($inventory['product_name']." only has ($available) available for the selected dates.");
          }
          
          //Insert reservation
          $reservationId = $RsvDB->createRSV($customerId,$startDate,$endDate,$address,$city,$zip);

          $RsvDB->updateRsvDetails($reservationId, $inventoryId, (int)$quantity);
          $RsvDB->commit();

          $success = "Reservation created! #" . $reservationId .
                    " • " . esc($inventory['product_name']) .
                    " x " . (int)$quantity .
                    " • Dates: " . esc($startDate) . " to " . esc($endDate);

          $_SESSION['customer_success'] = $success;

          // Reset form fields
          $firstName = $lastName = $phone = $email = $address = $city = $zip = "";
          $startDate = $endDate = $item = $quantity = "";

          header("Location: " . BASE_URL . "/index.php?page=admin");
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
            header("Location: " . BASE_URL . "/index.php?page=admin");
            exit;
        }
        catch(Exception $e)
        {
            if ($RsvDB->inTransaction()) {
                $RsvDB->rollBack();
            }

            error_log("General error in create reservation: " . $e->getMessage());
            $_SESSION['customer_error'] = "Error creating reservation. Please try again later.";
            header("Location: " . BASE_URL . "/index.php?page=admin");
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
        header("Location: " . BASE_URL . "/index.php?page=admin");
        exit;
    }
}
#endregion

#region Delete Inventory Item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_item'])) {
    $product_name = clean('product_name');
    if ($product_name === '') {
        $_SESSION['inventory_error'] = "Product name is required to delete an item.";
    } else {
        try {
            $deleted = $InvDB->deleteByProductName($product_name);
            $_SESSION['inventory_success'] =
                "Item '{$product_name}' deleted successfully.";
        } catch (Exception $e) {
            $_SESSION['inventory_error'] = $e->getMessage();
        }
    }
    header("Location: " . BASE_URL . "/index.php?page=admin");
    exit;
}
#endregion

#region Create Inventory Item
$product_name = $description = $type = $category = $price = $stock = $photo = "";
// ----------------------------
// Create an Inventory Item
// ----------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {

  $errors = [];
  $success = [];

  $product_name = clean('product_name');
  $description = clean('description');
  $type = clean('type');
  $category = clean('category');
  $price = clean('price','float');
  $stock = clean('stock','int');
  $photo = clean('photo','url');


    // --- Validation ---
    //---------------------------------------------------------------------------------	
    //									Item Name Validation
    //---------------------------------------------------------------------------------	
    if (empty($product_name)) {
        $errors[] = "Product name is required.";
    }
    //---------------------------------------------------------------------------------	
    //									Description Validation
    //---------------------------------------------------------------------------------	
    if (empty($description)) {
        $errors[] = "Description is required.";
    }
    //---------------------------------------------------------------------------------	
    //									Type Validation
    //---------------------------------------------------------------------------------	
    if (empty($type)) {
        $errors[] = "Type is required.";
    }
    //---------------------------------------------------------------------------------	
    //									Category Validation
    //---------------------------------------------------------------------------------	
    if (empty($category)) {
        $errors[] = "Category is required.";
    }
    //---------------------------------------------------------------------------------	
    //									Price Validation
    //---------------------------------------------------------------------------------	
    if(empty($price))
    {
        $errors[] =  "Price is required for" . $buttonChosen . ".";
    }
    if ($price === false || $price <= 0) {
        // Ensure price is positive
        $errors[] = "Price must be a positive number.";
    }
    // price has to have no more than two decimal places.
    else if ($price != number_format((float)$price, 2, '.', '')) {
        // Compare the raw value to its two-decimal formatted version
        $errors[] = "Price can only have two decimal places.";
    }
    //---------------------------------------------------------------------------------	
    //									Stock Validation
    //---------------------------------------------------------------------------------	
    if(empty($stock))
    {
        $errors[] =  "Stock Amount is required.";
    }
	// Stock cannot be less than 1
    else if(filter_var($stock, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) === false)
	{
	 	$errors[] = "Stock must be a whole number of at least 1.";
	}
    //---------------------------------------------------------------------------------	
    //									Image URL Validation
    //---------------------------------------------------------------------------------	
    if (empty($photo)) {
        $errors[] = "Photo URL is required.";
    }

    if (empty($errors)) {
            try {
                $InventoryItems->createProduct($product_name, $description, $type, $category, $price, $stock, $photo);
                $_SESSION['inventory_success'] = "Product created successfully";
            } catch (PDOException $e) {
                if ($e->getCode() == '23000') {
					error_log("Integrity constraint violation (23000) in createProduct: " . $e->getMessage());
                    $_SESSION['inventory_error'] = "Error: Unable to create order - Invalid item selection.";
                } else {
					error_log("Database error in createProduct: ". $e->getMessage());
                    $_SESSION['inventory_error'] = "Error: Unable to create order due to a database error." . $e;
                }
            } catch (Exception $e) {
				error_log("General error in createProduct: ". $e->getMessage());
                $_SESSION['inventory_error'] = "Error: Unable to create order - " . $e->getMessage();
            }		
		} else {
			$_SESSION['inventory_error'] = implode("<br>", $errors); //implode joins array elements into a string. Used for display
		}

    header("Location: " . BASE_URL . "/index.php?page=admin");
    exit;
}
#endregion Create Inventory Item  
?>

<?php
//Populate Item <select> from DB so it stays in sync with inventory
    $items = [];
    try
    {
      $items = $InvDB->populateItemsList();
    }
    catch(PDOException $e)
    { 
      error_log("Database error in populateItemsList: " . $e->getMessage());
    $_SESSION['customer_error'] = "Could not load item list."; 
  }
?>
<?php
#region SearchRsv

$show_results = false; // Only show table after search/all

$field_map = [
    "Reservation ID" => "reservation_id",
    "Phone" => "phone",
    "Address" => "address",
    "City" => "city",
    "Zip" => "zip",
    "Start Date" => "start_date",
    "End Date" => "end_date",
    "Order Status" => "order_status"
];

// ----------------------------
// Handle POST actions
// ----------------------------

    // Clear search
    if (isset($_POST['clear'])) 
    {
        unset($_SESSION['search_field'], $_SESSION['search_value']);
    }

    // Show all
    if (isset($_POST['all'])) 
    {
        unset($_SESSION['search_field'], $_SESSION['search_value']);
        $show_results = true;
    }

    // Search
    if (isset($_POST['search'])) 
    {
        $field = $_POST['field']; 
        $value = clean('search_value');

        $errors = [];

        if (empty($field) || empty($value)) {
            $errors[] = "Please select a field and enter a value.";
        } else {
            $_SESSION['search_field'] = $field;
            $_SESSION['search_value'] = $value;
            $show_results = true;
        }

            // If there are errors, save them as flash and redirect
        if (!empty($errors)) {
          $_SESSION['search_error'] = implode("<br>", $errors);
          header("Location: " . BASE_URL . "/index.php?page=admin"); // or the same page
          exit;
        }
    }

//---------------------------------------------------------------------------------	
//									            Fetch Reservations
//---------------------------------------------------------------------------------	

$reservations = [];

if ($show_results)
{

  $sql = $RsvDB->fetchRSVInfo();

  $params = [];
  $where = [];

  if(!empty($_SESSION['search_field']) && !empty($_SESSION['search_value']))
  {
    $humanField = $_SESSION['search_field'];
    $value = $_SESSION['search_value'];

    // Whitelist: ensure the selected field exists in the map
    if (!isset($field_map[$humanField]))
    {
      $errors[] = "Invalid search field.";
    }
    else
    {
      $column = $field_map[$humanField];

      // Date fields (stored as Y-m-d)
      $date_fields = ['Start Date', 'End Date'];
      if (in_array($humanField, $date_fields, true)) 
      {
        $date = DateTimeImmutable::createFromFormat('m/d/Y', $value);

        if ($date) $value = $date->format('Y-m-d');
        $where[] = "r.$column = :value";
        $params[':value'] = $value;
      }
      // Phone (search customers.phone)
      else if ($humanField === 'Phone')
      {
        $digits = preg_replace('/\D/', '', $value);
        $where[] = "REPLACE(REPLACE(REPLACE(c.phone,'-',''), '(', ''), ')', '') LIKE :value";
        $params[':value'] = "%$digits%";
      }
      // Other reservation fields (address, city, zip, status, reservation_id)
      else
      {
        // reservation_id is numeric – allow exact or LIKE? Keep LIKE for flexibility.
        $where[] = "r.$column LIKE :value";
        $params[':value'] = "%$value%";
      }
    }
  }

    if (!empty($where))
    {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    $reservations = $RsvDB->fetchRSV($sql, $params);
}
#endregion SearchRsv

#region Update Reservation
// Check: Maybe make these clean instead of htmlspecialchars$post stuff?
//---------------------------------------------------------------------------------	
//									       Update RSV
//---------------------------------------------------------------------------------	
    if (isset($_POST['update'])) 
    {
        $id = (int) $_POST['update'];

        // Directly grab array input values
        $phone   = htmlspecialchars(trim($_POST['phone'][$id] ?? ''));
        $address = htmlspecialchars(trim($_POST['address'][$id] ?? ''));
        $city    = htmlspecialchars(trim($_POST['city'][$id] ?? ''));
        $zip     = htmlspecialchars(trim($_POST['zip'][$id] ?? ''));
        $start   = htmlspecialchars(trim($_POST['start_date'][$id] ?? ''));
        $end     = htmlspecialchars(trim($_POST['end_date'][$id] ?? ''));
        $status  = htmlspecialchars(trim($_POST['order_status'][$id] ?? ''));

        // Require all fields
        if (empty($id) || empty($phone) || empty($address) || empty($city) || empty($zip) || empty($start) || empty($end) || empty($status)) 
        {
          $errors[] = "All fields are required for updating reservation $id.";
        }
        //---------------------------------------------------------------------------------	
        //									Status Validation
        //---------------------------------------------------------------------------------	
        if (!in_array($status, ['Pending','Completed','Canceled'], true))
        {
          $errors[] = "Order status must be 'Pending', 'Completed', or 'Canceled'.";
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

        //---------------------------------------------------------------------------------	
        //									Date Validation
        //---------------------------------------------------------------------------------	
        //Fix our timezone issue.
        date_default_timezone_set('America/Chicago');
        $startDate = $start ? DateTimeImmutable::createFromFormat('Y-m-d', $start) : false;
        $endDate = $end ? DateTimeImmutable::createFromFormat('Y-m-d', $end) : false;
        $today = new DateTimeImmutable('today');
        
        if (empty($start)) 
        {
          $errors[] = "Start date is required.";
        }
        else if($start && !$startDate)
        {
          $errors[] = "Start date format is invalid";
        }

        if(empty($end)) 
        {
          $errors[] = "End date is required.";
        } 
        else if($end && !$endDate)
        {
          $errors[] = "End date format is invalid";
        }	
        //Only run business rules if both dates parsed correctly
        if($startDate && $endDate)
        {
          //Days from TODAY to start
          $diffFromToday = (int)$today->diff($startDate)->format('%r%a');
          
          //Rental length: days between start & end
          $spanDays = (int)$startDate->diff($endDate)->format('%r%a');
          
          // Only enforce "start date must be at least 1 day after today"
          // if the reservation is NOT completed.
          if (strcasecmp($status, 'Completed') !== 0 && $diffFromToday <= 0)
          {
            $errors[] = "Start date must be at least 1 day after today.";
          }
          
          //End must be at least next day unless COMPLETED
          if(strcasecmp($status, 'Completed') !== 0 && $spanDays <= 0)
          {
            $errors[] = "End date must be at least 1 day after start date.";
          }
          
          //Max of a 3 day Rental
          if($spanDays > 3)
          {
            $errors[] = "Rental length cannot exceed 3 consecutive days due to equipment maintenance.";
          }
        }
        
        // Phone Validation
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
            // Normalize phone to ###-###-####
              $phone = substr($digits, 0, 3) . '-' . substr($digits, 3, 3) . '-' . substr($digits, 6, 4);
          }
        }
        
        if(empty($errors))
        {
          try
          {
            $RsvDB->beginTransaction();

            //Lock reservation row; get its customer_id
            $cid = $InvDB->updateItemCid($id);

            if($cid === null)
            {
              throw new Exception("Reservation not found for update.");
            }

            //Update customer phone
            $CustDB->updateCustomer($cid, $phone);

            //Update Reservation fields(addy,city,zip,dates,status,id)
            $RsvDB->updateRSV($address,$city,$zip,$start,$end,$status,$id);

            $RsvDB->commit();

            $_SESSION['update_success'] = "Reservation $id updated successfully.";
            header("Location: " . BASE_URL . "/index.php?page=admin");
            exit;
          }
          catch(Exception $e)
          {
            if($RsvDB->inTransaction())
            {
              $RsvDB->rollBack();
            }
            error_log("General error in updateItem: ".$e->getMessage());
            $_SESSION['update_error'] = "Could not update reservation: ". esc($e->getMessage());
            header("Location: " . BASE_URL . "/index.php?page=admin");
            exit;
          }
        }
        //// add errors to the flash error
        else
        {
          $_SESSION['update_error'] = implode("<br>", $errors);
          // keep/restore search so the same row shows up after redirect
          header("Location: " . BASE_URL . "/index.php?page=admin");
          exit;
        }
      }
#endregion Update Reservation



#region Delete Reservation
//---------------------------------------------------------------------------------	
//								            DELETE RSV
//---------------------------------------------------------------------------------	
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) 
{
    $reservation_id = (int) $_POST['delete'];  // Get the ID from the button value

    $errors = []; 
    $success = "";

    //Validate the reservation_Id
    if (!ctype_digit((string)$reservation_id)) 
    {
        $_SESSION['customer_error'] = "Invalid reservation id.";
        header("Location: " . $_SERVER['PHP_SELF']); exit;
    }
    $reservation_id = (int)$reservation_id;

    if(empty($errors))
    {
        try 
        {
            $RsvDB->beginTransaction();

            //Lock the row to prevent conocurrent changes while we decide/delete
            $status = $RsvDB->deleteCheck($reservation_id);

            //If reservation not found
            if($status === false)
            {
                throw new Exception("Reservation not found.");
            }

            //Cannot hard delete completed orders
            if(strcasecmp($status['order_status'], 'Completed') === 0)
            {
                throw new Exception("Completed reservations cannot be deleted.");
            }

            //Delete Children (rsv details table)
            $RsvDB->deleteRSVChild($reservation_id);

            //Delete Parent (reservation table)
            $deletedRows = $RsvDB->deleteRSVParent($reservation_id);

            if($deletedRows!==1)
            {
              throw new Exception("Delete failed or reservation not found.");
            }

            $RsvDB->commit();
            $_SESSION['customer_success'] = "Reservation #{$reservation_id} was deleted.";

        } 
        catch (Exception $e) 
        {
            if($RsvDB->inTransaction())
            {
                $RsvDB->rollBack();
            }
            error_log("General error in deleteItem: ".$e->getMessage());
            $_SESSION['customer_error'] = "Could not delete reservation: " . esc($e->getMessage());
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
require_once BASE_PATH.'/src/includes/adminheader.php';
#endregion Delete Reservation
?>

  <style>
    body {
      margin: 0;
      /* font-family: Arial, sans-serif; */
      /* background: linear-gradient(135deg, #a100ff 0%, #ff6ec7 60%, #f9d423 100%);
      color: #222; */
      /* min-height: 100vh; */
      display: flex;
      flex-direction: column;
    }
    
    header .logo {
      font-size: 1.2rem;
      /* font-weight: bold; */
      text-decoration: none;
      color: #a100ff;
    }

    main.container {
      flex: 1;
      display: grid;
      grid-template-columns: 280px 1fr;
      gap: 1rem;
      padding: 1rem;
    }

    aside.admin-panel, section.admin-panel {
      background: white;
      border-radius: 10px;
      padding: 1rem;
      box-shadow: 0 3px 8px rgba(0,0,0,0.1);
      overflow: auto;
    }

    h2 {
      margin: 0 0 0.8rem;
      font-size: 1.1rem;
      color: #a100ff;
    }

    /* .ribbon {
      background: #ff6ec7;
      color: white;
      padding: 0.3rem 0.6rem;
      border-radius: 4px;
      font-size: 0.9rem;
      margin-bottom: 0.5rem;
      display: inline-block;
    } */

    form.admin-form {
      display: grid;
      gap: 0.6rem;
    }
    form.admin-form .grid {
      display: grid;
      gap: 0.5rem;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    }
    label {
      display: flex;
      flex-direction: column;
      font-size: 0.85rem;
    }
    input, select, textarea {
      padding: 0.4rem;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 0.9rem;
    }

    .admin-actions {
      margin-top: 0.8rem;
      display: flex;
      flex-wrap: wrap;
      gap: 0.4rem;
    }
    .btn {
      border: none;
      border-radius: 5px;
      padding: 0.45rem 0.8rem;
      cursor: pointer;
      font-weight: bold;
      font-size: 0.85rem;
    }
    .btn-primary { background: #a100ff; color: white; }
    .btn-secondary { background: #ff6ec7; color: white; }
    .btn-danger { background: #f9d423; color: #222; }

    table.results-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
      font-size: 0.9rem;
    }
    table.results-table th, table.results-table td {
      /* border: 1px solid #ddd; */
      border: 2px solid  #FFF;
      padding: 0.5rem;
      text-align: left;
    }
    table.results-table th {
      background: #a100ff;
      color: white;
    }
    .results-wrap {
      overflow-x: auto;
    }

    footer.site-footer {
      text-align: center;
      padding: 0.8rem;
      background: rgba(255, 255, 255, 0.9);
      border-top: 3px solid #f9d423;
      font-size: 0.85rem;
    }

    .errorMsg{
      color: white;
      font-weight: bold;
      margin-left: 10px;
    }

    /* Responsive adjustments */
@media (max-width: 768px) {
  main.container {
    grid-template-columns: 1fr;
    padding: 0.5rem;
  }

  aside.admin-panel, section.admin-panel {
    padding: 0.5rem;
  }

  form.admin-form .grid {
    grid-template-columns: 1fr; /* stack inputs */
    gap: 0.5rem;
  }

  form.admin-form label {
    width: 100%;
    max-width: 400px; /* limit how wide inputs grow */
    /* remove centering */
  }

  input, select, textarea {
    width: 100%;
    max-width: 400px; /* same max width as label */
    box-sizing: border-box; /* include padding/border in width */
    font-size: 0.85rem;
  }

  .admin-actions {
    flex-direction: column;
    gap: 0.3rem;
    align-items: flex-start; /* align buttons to left */
  }

  .admin-actions .btn {
    width: 100%;
    max-width: 200px; /* limit button width */
  }

  table.results-table {
    font-size: 0.8rem;
  }

  table.results-table th, table.results-table td {
    padding: 0.3rem;
  }
}
</style>  


  <?php if ($flash_error): ?>
    <p class="errorMsg"><?= $flash_error ?></p>
  <?php endif; ?>

  <?php if ($flash_success): ?>
    <p class="errorMsg"><?= $flash_success ?></p>
  <?php endif; ?>

  <main>
    <!-- Add Reservation Form -->
      <h2 class ="adminHeader">Add Reservation</h2>
      <form action="../src/admin.php" method="post" id="createReservation" class="admin-form">
        <div class="grid">
          <label>First Name<input id="firstName" type="text" name="firstName" value="<?php echo esc($firstName); ?>"></label>
          <label>Last Name<input id="lastName" type="text" name="lastName" value="<?php echo esc($lastName); ?>"></label>
          <label>Phone<input id="phone" type="text" name="phone" value="<?php echo esc($phone); ?>"></label>
          <label>Email<input id="email" type="text" name="email" value="<?php echo esc($email); ?>"></label>
          <label>Street Address<input id="address" type="text" name="address" value="<?php echo esc($address); ?>"></label>
          <label>City<input id="city" type="text" name="city" value="<?php echo esc($city); ?>"></label>
          <label>Zipcode<input id="zip" type="text" name="zip" value="<?php echo esc($zip); ?>"></label>
          <label>Start Date<input id="startDate" type="date" name="startDate" value="<?= esc($startDate) ?>"></label>
          <label>End Date<input id="endDate" type="date" name="endDate" value="<?= esc($endDate) ?>"></label>
          <label>Item<select id="item" name="item">
            <option value="">Select...</option>
            <?php foreach ($items as $name): ?>
              <option value="<?php echo esc($name); ?>" <?php if ($item === $name) echo 'selected'; ?>>
                <?php echo esc($name); ?>
              </option>
            <?php endforeach; ?>
          </select></label>
          <label>Quantity <input id="quantity" type="number" name="quantity" value="<?php echo esc($quantity); ?>"></label>
        </div>
        <div class="admin-actions">
          <button type="submit" name="create" value="1" class="btn btn-primary">Create</button>
          <button type="reset" class="btn btn-secondary">Reset</button>
        </div>
      </form>

      <!-- Inventory Form -->
      <h2 class ="adminHeader" style="margin-top:1rem">Inventory</h2>
      <form action="../src/admin.php" method="post" class="admin-form">
        <div class="grid">
          <label>Product Name<input type="text" name="product_name" value="<?php echo esc($product_name); ?>"></label>
          <label>Description<input type="text" name="description" value="<?php echo esc($description); ?>"></label>
          <label>Type<select name="type"><br><br>
                <option value="">Select...</option>
                <option value="Wet" <?php if ($type === 'Wet') echo 'selected'; ?>>Wet</option>
                <option value="Dry" <?php if ($type === 'Dry') echo 'selected'; ?>>Dry</option>
            </select></label>
          <label>Category<select name="category"><br><br>
                <option value="">Select...</option>
                <option value="Bounce House" <?php if ($category === 'Bounce House') echo 'selected'; ?>>Bounce House</option>
                <option value="Slide" <?php if ($category === 'Slide') echo 'selected'; ?>>Slide</option>
                <option value="Obstacle" <?php if ($category === 'Obstacle') echo 'selected'; ?>>Obstacle</option>
            </select></label>
          <label>Price<input type = "number" name="price" value = "<?php echo esc($price); ?>"></label>
          <label>Stock<input type = "number" name="stock" value = "<?php echo esc($stock); ?>"></label>
          <label>Photo<input type = "text" name="photo" value = "<?php echo esc($photo); ?>"></label>
        </div>
        <div class="admin-actions">
          <button type="submit" name="add" class="btn btn-secondary">Add Item</button>
          <button class="btn btn-secondary">Edit Item</button>
                    <button type="submit" name="delete_item" value="1" class="btn btn-danger"onclick="return confirm('This will permanently delete the item. Continue?');">Delete Item</button>
      <!--<button class="btn btn-danger">Delete Item</button>-->
        </div>
      </form><br><br>
   <!-- </aside>-->

<!-- Search Form -->
<section class="admin-panel" id="adminSearchBorder">
  <h2>Search Reservations</h2>
  <form action="<?php echo $BASE_URL?>admin.php" method="post" class="admin-form" id="adminSearchForm">
    <div>
      <label for="search_field">Field</label>
      <select name="field" id="search_field">
        <option value="">Select...</option>
        <?php foreach ($field_map as $display => $db_col): 
          $selected = (isset($_POST['field']) && $_POST['field'] == $display) ||
                      (!isset($_POST['field']) && isset($_SESSION['search_field']) && $_SESSION['search_field'] == $display)
                      ? 'selected' : '';
        ?>
          <option value="<?= esc($display) ?>" <?= $selected ?>><?= esc($display) ?></option>
        <?php endforeach; ?>
      </select><br><br>

      <?php
        $selectedField = $_POST['field'] ?? $_SESSION['search_field'] ?? '';
        $date_fields = ['Start Date', 'End Date'];
        $input_type = in_array($selectedField, $date_fields) ? 'date' : 'text';
        $input_value = $_POST['search_value'] ?? $_SESSION['search_value'] ?? '';
      ?>
      <label for="search_value">Value</label>
      <input id="search_value" type="<?= $input_type ?>" name="search_value" value="<?= esc($input_value) ?>"><br><br>

      <div>
        <button class="btn btn-primary" type="submit" name="search">Search</button>
        <button type="submit" name="all">All</button>
        <button class="btn btn-secondary" type="submit" name="clear">Clear</button>
      </div>
    </div>
  </form>

<!-- Results Table -->
<form method="post">
  <table class="results-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Phone</th>
        <th>Address</th>
        <th>City</th>
        <th>Zip</th>
        <th>Start</th>
        <th>End</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($reservations)): ?>
        <?php foreach ($reservations as $res): ?>
          <tr>
            <td>
              <?= esc($res['reservation_id']) ?>
              <input type="hidden" name="reservation_id[<?= $res['reservation_id'] ?>]" value="<?= esc($res['reservation_id']) ?>">
            </td>
            <td><input type="text" name="phone[<?= $res['reservation_id'] ?>]" value="<?= esc($res['phone']) ?>"></td>
            <td><input type="text" name="address[<?= $res['reservation_id'] ?>]" value="<?= esc($res['address']) ?>"></td>
            <td><input type="text" name="city[<?= $res['reservation_id'] ?>]" value="<?= esc($res['city']) ?>"></td>
            <td><input type="text" name="zip[<?= $res['reservation_id'] ?>]" value="<?= esc($res['zip']) ?>"></td>
            <td><input type="date" name="start_date[<?= $res['reservation_id'] ?>]" value="<?= esc($res['start_date']) ?>"></td>
            <td><input type="date" name="end_date[<?= $res['reservation_id'] ?>]" value="<?= esc($res['end_date']) ?>"></td>
            <td>
              <select name="order_status[<?= $res['reservation_id'] ?>]">
                <?php foreach (['Pending','Completed','Canceled'] as $opt): ?>
                  <option value="<?= $opt ?>" <?= $res['order_status']===$opt ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
              </select>
            </td>
            <td>
              <button type="submit" name="update" value="<?= $res['reservation_id'] ?>">Update</button><br><br>
              <button type="submit" name="delete" value="<?= $res['reservation_id'] ?>">Delete</button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- Empty row to maintain table size -->
        <tr>
          <?php for ($i = 0; $i < 9; $i++): ?>
            <td><input type="text" disabled></td>
          <?php endfor; ?>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</form>


</section>
</main>

<?php
    require_once BASE_PATH. '/src/includes/footer.php';
?>