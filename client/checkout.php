<?php
  session_name("sess_id");
  session_start();
  if(!isset($_SESSION['user_id'])){
    Header("Location: ./index.php");
    die();
  } else {
    require_once '../api/api.php';
    $api = new api();
    $client_id = $api->clean($_SESSION['client_id']);
    $order_id = $api->clean($_SESSION['order_id']);
  }

  // navigation guard
  if(empty($_SESSION['order_id'])){
    $_SESSION['res'] = "Illegal navigation error: You cannot checkout without an ongoing workorder.";
    Header("Location: ./orders.php");
  } else {
    try {
      // retrieving workorder details
      $statement = $api->prepare("SELECT client_fname, client_lname, order_date, amount_due_total, incentive FROM (workorder JOIN client ON workorder.client_id=client.client_id) WHERE workorder.order_id=? AND workorder.client_id=?");
      if ($statement===false) {
          throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
      }

      $mysqli_checks = $api->bind_params($statement, "ss", array($order_id, $client_id));
      if ($mysqli_checks===false) {
          throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
      }

      $mysqli_checks = $api->execute($statement);
      if($mysqli_checks===false) {
          throw new Exception('Execute error: The prepared statement could not be executed.');
      }

      $order = $api->get_result($statement);
      if($order===false){
        throw new Exception('get_result() error: Getting result set from statement failed.');
      }

      if($api->num_rows($order) > 0){
        $workorder = $api->fetch_assoc($order);
      }

      $api->free_result($statement);
      $mysqli_checks = $api->close($statement);
      if ($mysqli_checks===false) {
        throw new Exception('The prepared statement could not be closed.');
      } else {
        $statement = null;
      }

      // retrieving order items
      $statement = $api->prepare("SELECT order_item.item_id, tattoo_name, tattoo_image, tattoo_price, tattoo_quantity, order_item.tattoo_width, order_item.tattoo_height, paid, item_status, amount_addon FROM ((order_item INNER JOIN tattoo ON order_item.tattoo_id=tattoo.tattoo_id) LEFT JOIN reservation ON order_item.item_id=reservation.item_id) WHERE order_id=? ORDER BY item_status ASC, paid ASC");
      if ($statement===false) {
          throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
      }

      $mysqli_checks = $api->bind_params($statement, "s", $order_id);
      if ($mysqli_checks===false) {
          throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
      }

      $mysqli_checks = $api->execute($statement);
      if($mysqli_checks===false) {
          throw new Exception('Execute error: The prepared statement could not be executed.');
      }

      $items = $api->get_result($statement);
      if($items===false){
        throw new Exception('get_result() error: Getting result set from statement failed.');
      }

      $api->free_result($statement);
      $mysqli_checks = $api->close($statement);
      if ($mysqli_checks===false) {
        throw new Exception('The prepared statement could not be closed.');
      } else {
        $statement = null;
      }

      echo (strcasecmp($workorder['incentive'], "15% Discount") == 0) ? "<script>var discounted = true;</script>" : "<script>var discounted = false;</script>";
    } catch (Exception $e) {
        exit();
        $_SESSION['res'] = $e->getMessage();
        Header("Location: ./orders.php");
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Libre+Caslon+Text:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  
  <!-- bootstrap -->
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
  
  <!-- native style -->
  <link href="../style/bootstrap.css" rel="stylesheet">
  <link href="../style/style.css" rel="stylesheet">
  <style>
    .tattoo-image {
      max-width: 275x;
      max-height: 275px;
      width: 275px;
      height: 275px;
      background-position: center;
      background-repeat: no-repeat;
      background-size: cover;
    }
  </style>
  <title>Order Checkout | NJC Tattoo</title>
</head>
<body>
  <header class="header">
    <nav class="nav-bar row mx-0">
      <ul class="col my-0" id="nav-links">
        <li><a href="./explore.php">Explore</a></li>
        <li class="active"><a href="orders.php">Orders</a></li>
        <li><a href="./reservations.php">Bookings</a></li>
      </ul>
      <div class="col d-flex align-items-center justify-content-end my-0 mx-5">
        <div class="btn-group" id="nav-user">
          <button type="button" class="btn p-0" data-bs-toggle="dropdown" aria-expanded="false"><span class="material-icons lh-base display-5">account_circle</span></button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="user.php">Profile</a></li>
              <li>
                <form action="../api/queries.php" method="post">
                  <button type="submit" class="dropdown-item btn-link" name="logout">Sign Out</button>
                </form>
              </li>
            </ul>
        </div>
      </div>
    </nav>
  </header>
  <div class="content w-70">
    <form action="../api/queries.php" method="POST">
      <div class="pb-6 border-bottom">
        <h2 class="fw-bold display-3">Checkout</h2>
        <p class="d-inline fs-5 text-muted">Pay for your ongoing tattoo orders here. Tick the checkboxes of the items you wish to include in your payment.</p>
      </div>
      <?php
        $item_count = 0;

        if($api->num_rows($items) > 0){
          while($item = $api->fetch_assoc($items)){
            if((strcasecmp($item['item_status'], "Standing") == 0 && strcasecmp($item['paid'], "Unpaid") == 0) || (in_array($item['item_status'], array("Reserved", "Applied")) && in_array($item['paid'], array("Unpaid", "Partially Paid")))){
              $item_count++;
      ?>
      <div class="border-bottom d-flex align-items-center justify-content-between p-5">
        <div class="d-flex align-items-center">
          <div class="me-4">
            <input type="hidden" class="d-none" name="index[]" value="<?php echo $item['item_id']?>" />
            <input type="checkbox" class="form-check-input p-2" name="item[]" value="<?php echo $item['item_id']?>" checked/>
          </div>
          <!-- tattoo image -->
          <div class="tattoo-image rounded-pill shadow-sm" style="background-image: url(<?php echo $api->clean($item['tattoo_image']); ?>)"></div>
        </div>
        <div class="w-100 ms-6">
          <div class="row my-5">
            <!-- tattoo name -->
            <div class="col">
              <label class="form-label fw-semibold">Item</label>
              <p><?php echo $api->clean($item['tattoo_width']) . "x" . $api->clean($item['tattoo_height']) . " " . $api->clean($item['tattoo_name']) ?></p>
            </div>
            <!-- item status -->
            <div class="col">
              <label for="status" class="form-label fw-semibold">Item Status</label>
              <p><?php echo $api->clean($item['item_status']) ?></p>
            </div>
            <!-- payment status -->
            <div class="col">
              <label class="form-label fw-semibold">Payment Status</label>
              <p><?php echo $api->clean($item['paid']); ?></p>
            </div>
          </div>
          <div class="row my-5">
            <!-- quantity -->
            <div class="col">
              <label for="quantity" class="form-label fw-semibold">Quantity</label>
              <input type="hidden" class="d-none" value="<?php echo $api->clean($item['tattoo_quantity']) ?>" min="<?php echo $api->clean($item['tattoo_quantity']) ?>" max="<?php echo $api->clean($item['tattoo_quantity']) ?>" name="quantity[]" />
              <input type="number" class="quantity form-control" value="<?php echo $api->clean($item['tattoo_quantity']) ?>" min="1" max="<?php echo $api->clean($item['tattoo_quantity']) ?>" name="checkout_quantity[]" />
            </div>
            <!-- price -->
            <div class="col">
              <label for="quantity" class="form-label fw-semibold">Price</label>
              <p class="prices">₱<?php echo $api->clean($item['tattoo_price']) ?></p>
            </div>
            <!-- amount_addon -->
            <div class="col">
              <label for="status" class="form-label fw-semibold">Reservation Addon</label>
              <p class="addons"><?php echo ($item['amount_addon'] == 0) ? "N/A" : "₱".$api->clean($item['amount_addon']); ?></p>
            </div>
          </div>
        </div>
      </div>
      <?php }}} 
        if($item_count == 0){ ?>
          <div class="border-bottom px-5 py-8 no-select">
            <h1 class="display-3 fst-italic text-muted">No items available for payment.</h1>
          </div>
      <?php } ?>
      <div class="row mx-0 p-5">
        <!-- order id -->
        <div class="col">
          <label class="form-label fw-semibold">Order ID</label>
          <p><?php echo $api->clean($order_id) ?></p>
        </div>
        <?php
          $timestamp = explode(' ', $api->clean($workorder['order_date']));
          $date = date("M:d:Y", strtotime($timestamp[0]));
          $time = date("g:i A", strtotime($timestamp[1]));
          $date = explode(':', $date);
        ?>
        <div class="col">
          <label class="form-label fw-semibold">Placed on</label>
          <p><?php echo $api->clean($date[0]) . " " . $api->clean($date[1]) . ", " . $api->clean($date[2]) . ", " . $api->clean($time) ?></p>
        </div>
        <!-- 15% discount -->
        <div class="col">
          <label class="form-label fw-semibold">15% Discount?</label>
          <p class="<?php echo (strcasecmp($workorder['incentive'], "15% Discount") == 0) ? "text-success" : "text-muted"; ?> fw-semibold" id="discount"><?php echo (strcasecmp($workorder['incentive'], "15% Discount") == 0) ? "Yes" : "No"; ?></p>
        </div>
        <!-- amount due total -->
        <div class="col">
          <label class="form-label fw-semibold">Amount Due Total</label>
          <p id="total">₱<?php echo $workorder['amount_due_total'] ?></p>
        </div>
      </div>
      <hr class="mb-5" />
      <div class="mx-2">
        <div class="row my-4">
          <h4 class="mb-3">Client Name</h4>
          <div class="col">
            <label class="form-label text-muted" for="first_name">First Name</label>
            <input type="text" class="form-control" value="<?php echo $workorder['client_fname']?>" <?php if($item_count == 0){ echo "disabled"; }?> name="first_name" minlength="2" maxlength="50" required>
            <p class="my-2 <?php echo isset($_SESSION['first_name_err']) ? "d-block" : "d-none"; ?> text-danger height_err"><?php if(isset($_SESSION['first_name_err'])){ echo $_SESSION['first_name_err']; } ?></p>
          </div>
          <div class="col">
            <label class="form-label text-muted" for="last_name">Last Name</label>
            <input type="text" class="form-control" value="<?php echo $workorder['client_lname']?>" <?php if($item_count == 0){ echo "disabled"; }?> name="last_name" minlength="2" maxlength="50" required>
            <p class="my-2 <?php echo isset($_SESSION['last_name_err']) ? "d-block" : "d-none"; ?> text-danger height_err"><?php if(isset($_SESSION['last_name_err'])){ echo $_SESSION['last_name_err']; } ?></p>
          </div>
        </div>
        <div class="row my-3">
          <h4 class="mb-3">Billing Address</h4>
          <div class="col">
            <label class="form-label text-muted" for="street_address">Street Address</label>
            <input type="text" class="form-control" <?php if($item_count == 0){ echo "disabled"; }?> name="street_address" maxlength="255" required>
            <p class="my-2 <?php echo isset($_SESSION['street_address_err']) ? "d-block" : "d-none"; ?> text-danger height_err"><?php if(isset($_SESSION['street_address_err'])){ echo $_SESSION['street_address_err']; } ?></p>
          </div>
          <div class="col">
            <label class="form-label text-muted" for="city">City</label>
            <input type="text" class="form-control" <?php if($item_count == 0){ echo "disabled"; }?> name="city" maxlength="35" required>
            <p class="my-2 <?php echo isset($_SESSION['city_err']) ? "d-block" : "d-none"; ?> text-danger height_err"><?php if(isset($_SESSION['city_err'])){ echo $_SESSION['city_err']; } ?></p>
          </div>
        </div>
        <div class="row my-3">
          <div class="col">
            <label class="form-label text-muted" for="province">Province</label>
            <input type="text" class="form-control" <?php if($item_count == 0){ echo "disabled"; }?> name="province" maxlength="35">
            <p class="my-2 <?php echo isset($_SESSION['province_err']) ? "d-block" : "d-none"; ?> text-danger height_err"><?php if(isset($_SESSION['province_err'])){ echo $_SESSION['province_err']; } ?></p>
          </div>
          <div class="col">
            <label class="form-label text-muted" for="zip">Postal / Zip Code</label>
            <input type="text" class="form-control" value="6000" <?php if($item_count == 0){ echo "disabled"; }?> name="zip" minlength="4" maxlength="4">
            <p class="my-2 <?php echo isset($_SESSION['zip_err']) ? "d-block" : "d-none"; ?> text-danger height_err"><?php if(isset($_SESSION['zip_err'])){ echo $_SESSION['zip_err']; } ?></p>
          </div>
        </div>
        <div class="row my-4">
          <div class="col">
            <h4 class="mb-3">Payment Amount</h4>
            <div class="input-group">
              <span class="input-group-text">₱</span>
              <input type="number" class="form-control" <?php if($item_count == 0){ echo "disabled"; }?> name="amount_paid" required>
            </div>
            <p class="my-2 <?php echo isset($_SESSION['amount_paid_err']) ? "d-block" : "d-none"; ?> text-danger height_err"><?php if(isset($_SESSION['amount_paid_err'])){ echo $_SESSION['amount_paid_err']; } ?></p>
          </div>
          <div class="col">
            <h4 class="mb-3">Payment Method</h4>
            <div class="form-check form-check-inline mt-2">
              <input class="form-check-input" type="radio" <?php if($item_count == 0){ echo "disabled"; }?> name="payment_method" value="Debit" required>
              <label class="form-check-label" for="inlineRadio1"><i class="far fa-credit-card"></i>Debit Card</label>
            </div>
            <div class="form-check form-check-inline mt-2">
              <input class="form-check-input" type="radio" <?php if($item_count == 0){ echo "disabled"; }?> name="payment_method" value="Credit" required>
              <label class="form-check-label" for="inlineRadio2"><i class="far fa-credit-card"></i>Credit Card</label>
            </div>
            <div class="form-check form-check-inline mt-2">
              <input class="form-check-input" type="radio" <?php if($item_count == 0){ echo "disabled"; }?> name="payment_method" value="Prepaid" required>
              <label class="form-check-label" for="inlineRadio2"><i class="far fa-credit-card"></i>Prepaid Card</label>
            </div>
            <p class="my-2 <?php echo isset($_SESSION['payment_method_err']) ? "d-block" : "d-none"; ?> text-danger height_err"><?php if(isset($_SESSION['payment_method_err'])){ echo $_SESSION['payment_method_err']; } ?></p>
          </div>
        </div>
        <div class="row my-4">
          <div class="col">
            <h4 class="mb-3">Card Number</h4>
            <input type="text" inputmode="numeric" class="form-control" <?php if($item_count == 0){ echo "disabled"; }?> name="card_number" minlength="11" maxlength="16" required>
            <p class="my-2 <?php echo isset($_SESSION['card_number_err']) ? "d-block" : "d-none"; ?> text-danger height_err"><?php if(isset($_SESSION['card_number_err'])){ echo $_SESSION['card_number_err']; } ?></p>
          </div>
          <div class="col">
            <h4 class="mb-3">PIN</h4>
            <input type="password" inputmode="numeric" class="form-control" <?php if($item_count == 0){ echo "disabled"; }?> name="pin" minlength="6" maxlength="6" required>
            <p class="my-2 <?php echo isset($_SESSION['pin_err']) ? "d-block" : "d-none"; ?> text-danger height_err"><?php if(isset($_SESSION['pin_err'])){ echo $_SESSION['pin_err']; } ?></p>
          </div>
          <div class="col">
            <h4 class="mb-3">Bank Name</h4>
            <input type="text" class="form-control" <?php if($item_count == 0){ echo "disabled"; }?> name="bank_name" required>
            <p class="my-2 <?php echo isset($_SESSION['bank_name_err']) ? "d-block" : "d-none"; ?> text-danger height_err"><?php if(isset($_SESSION['bank_name_err'])){ echo $_SESSION['bank_name_err']; } ?></p>
          </div>
        </div>
      </div>
      <hr class="my-5" />
      <button type="submit" name="checkout" class="btn btn-primary btn-lg rounded-pill"<?php if($item_count == 0){ echo "disabled"; }?>>Checkout</button>
    </form>
  </div>
</body>
<script>
  var items = Array.from(document.querySelectorAll('input[type=checkbox].form-check-input'));
  var quantity = document.getElementsByClassName('quantity');
  var prices = Array.from(document.querySelectorAll('.prices'));
  var addons = Array.from(document.querySelectorAll('.addons'));
  var amount_due_total = document.getElementById('total');
  var checkout = document.getElementsByName('checkout')[0];

  var checked = [];
  var total = 0.00;

  for(var i=0, count=items.length; i < count; i++){
    checked = items.map(item => items.indexOf(item));
    prices[i] = parseFloat((prices[i].innerText || textContent).substring(1));
    addons[i] = addons[i].innerText || textContent;
  }

  for(var i=0, item_count=items.length; i < item_count; i++){
    items[i].addEventListener('change', function() {
      item_index = items.indexOf(this);
      
      if(this.checked) {
        checked.push(item_index);
      } else {
        var index = checked.indexOf(item_index);
        if (index > -1) {
          checked.splice(index, 1);
        }
      }

      total = 0.00;
      for(var j=0, count=checked.length; j < count; j++){
        total+= parseInt(quantity[checked[j]].value) * prices[checked[j]];

        if(!(addons[checked[j]].localeCompare("N/A") == 0)){
          total+= parseFloat(addons[checked[j]].substring(1));
        }        
      }

      if(discounted){
        total -= (total * .15); 
      }

      amount_due_total.innerText = "₱".concat((total.toFixed(2)).toString());
      if(checked.length < 1){
        checkout.disabled = true;
      } else {
        checkout.disabled = false;
      }
    });
  }
</script>
<script src="../api/bootstrap-bundle-min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> -->
</html>
<?php
  if(isset($_SESSION['first_name_err'])){
    unset($_SESSION['first_name_err']);
  }
  if(isset($_SESSION['last_name_err'])){
    unset($_SESSION['last_name_err']);
  }
  if(isset($_SESSION['street_address_err'])){
    unset($_SESSION['street_address_err']);
  }
  if(isset($_SESSION['city_err'])){
    unset($_SESSION['city_err']);
  }
  if(isset($_SESSION['province_err'])){
    unset($_SESSION['province_err']);
  }
  if(isset($_SESSION['zip_err'])){
    unset($_SESSION['zip_err']);
  }
  if(isset($_SESSION['amount_paid_err'])){
    unset($_SESSION['amount_paid_err']);
  }
  if(isset($_SESSION['payment_method_err'])){
    unset($_SESSION['payment_method_err']);
  }
  if(isset($_SESSION['card_number_err'])){
    unset($_SESSION['card_number_err']);
  }
  if(isset($_SESSION['pin_err'])){
    unset($_SESSION['pin_err']);
  }
  if(isset($_SESSION['bank_name_err'])){
    unset($_SESSION['bank_name_err']);
  }
  if(isset($_SESSION['res'])){
    unset($_SESSION['res']);
  }
?>