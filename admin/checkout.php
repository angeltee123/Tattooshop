<?php
  session_name("sess_id");
  session_start();
  // navigation guard
  // if(!isset($_SESSION['user_id']) || strcasecmp($_SESSION['user_type'], "User") == 0){
  //   Header("Location: ./orders.php");
  //   die();
  // }

  // elseif(empty($_POST)){
  //   $_SESSION['res'] = "Cannot log client payment without order details provided.";
  //   Header("Location: ./orders.php");
  //   die();
  // }

  // else {
    require_once '../api/api.php';
    $api = new api();
    $client_id = $api->clean($_POST['client_id']);
    $order_id = $api->clean($_POST['order_id']);
  // }

  try {
    // retrieve order data
    $statement = $api->prepare("SELECT client_fname, client_lname, order_date, amount_due_total, incentive FROM (workorder JOIN client ON workorder.client_id=client.client_id) WHERE order_id=? AND workorder.client_id=? ORDER BY order_date ASC LIMIT 1");
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

    $res = $api->get_result($statement);
    if($res===false){
        throw new Exception('get_result() error: Getting result set from statement failed.');
    }

    if($api->num_rows($res) > 0){
      $order = $api->fetch_assoc($res);
    } else {
      throw new Exception('No workorder under the given IDs found.');
    }

    $api->free_result($statement);
    $mysqli_checks = $api->close($statement);
    if ($mysqli_checks===false) {
    throw new Exception('The prepared statement could not be closed.');
    } else {
        $res = null;
        $statement = null;
    }
  } catch (Exception $e) {
      exit();
      $_SESSION['res'] = $e->getMessage();
      Header("Location: ./orders.php");
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
  <title>Payment Logging | NJC Tattoo</title>
</head>
<body>
  <header class="header border-bottom border-2">
    <nav class="nav-bar row mx-0">
      <ul class="col my-0" id="nav-links">
        <li><a href="catalogue.php">Catalogue</a></li>
        <li class="active"><a href="orders.php">Orders</a></li>
        <li><a href="reservations.php">Bookings</a></li>
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
  <form action="./queries.php" class="w-100 my-5" method="POST">
    <div class="w-70 mx-auto border">
      <div class="row p-5">
        <div class="col">
          <label class="form-label fw-semibold">Order ID</label>
          <p><?php echo $api->clean($order_id) ?></p>
        </div>
        <div class="col">
          <?php
              $datetime = explode('-', $api->clean($order['order_date']));
              $date = date("M:d:Y", strtotime($datetime[0]));
              $time = date("g:i A", strtotime($datetime[1]));
              $date = explode(':', $date);
            ?>
          <label class="form-label fw-semibold">Placed on</label>
          <p><?php echo $api->clean($date[0]) . " " . $api->clean($date[1]) . ", " . $api->clean($date[2]) . ", " . $api->clean($time) ?></p>
        </div>
        <div class="col">
          <label class="form-label fw-semibold">Incentive</label>
          <p><?php echo $api->clean($order['incentive']) ?></p>
        </div>
      </div>
      <?php
        try {    
          $get_items = $api->prepare("SELECT order_item.item_id, tattoo_name, tattoo_image, tattoo_price, tattoo_quantity, order_item.tattoo_width, order_item.tattoo_height, paid, item_status, amount_addon FROM ((order_item INNER JOIN tattoo ON order_item.tattoo_id=tattoo.tattoo_id) LEFT JOIN reservation ON order_item.item_id=reservation.item_id) WHERE order_id=? ORDER BY paid ASC, item_status DESC");
          if ($get_items===false) {
              throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
          }

          $mysqli_checks = $api->bind_params($get_items, "s", $order_id);
          if ($mysqli_checks===false) {
              throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
          }

          $mysqli_checks = $api->execute($get_items);
          if($mysqli_checks===false) {
              throw new Exception('Execute error: The prepared statement could not be executed.');
          }

          $items = $api->get_result($get_items);
          if($items===false){
            throw new Exception('get_result() error: Getting result set from statement failed.');
          }
        } catch (Exception $e) {
            exit();
            $_SESSION['res'] = $e->getMessage();
            Header("Location: ./index.php");
        }

        if($api->num_rows($items) > 0){
          while($item = $api->fetch_assoc($items)){
            if((strcasecmp($item['item_status'], "Standing") == 0 && strcasecmp($item['paid'], "Unpaid") == 0) || (in_array($item['item_status'], array("Reserved", "Applied")) && in_array($item['paid'], array("Unpaid", "Partially Paid")))){
      ?>
        <div class="border-top d-flex align-items-center justify-content-between p-5">
          <!-- checkbox -->
          <div class="me-5">
            <input type="hidden" class="d-none" name="index[]" value="<?php echo $item['item_id']?>" />
            <input type="checkbox" class="form-check-input p-2" name="item[]" value="<?php echo $item['item_id']?>" checked/>
          </div>
          <!-- tattoo image -->
          <div class="tattoo-image shadow-sm border-2 rounded" style="background-image: url(<?php echo $api->clean($item['tattoo_image']); ?>)"></div>
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
      <?php }}} ?>
      <div class="border-top row p-5">
        <div class="col">
          <label class="form-label fw-semibold">15% Discount?</label>
          <p><?php echo (strcasecmp($order['incentive'], "15% Discount") == 0) ? "Yes" : "No"; ?></p>
        </div>
        <div class="col">
          <label class="form-label fw-semibold">Amount Due Total</label>
          <p id="total">₱<?php echo $order['amount_due_total'] ?></p>
        </div>
      </div>
    </div>
    <div class="mx-auto w-70 my-5">
      <div class="row mx-auto w-100">
        <div class="row my-3">
          <h4 class="mb-3">Client Name</h4>
          <div class="col">
            <input type="text" class="form-control my-2" value="<?php echo $order['client_fname']?>" name="first_name" minlength="2" maxlength="50" required>
            <label class="form-label text-muted">First Name</label>
          </div>
          <div class="col">
            <input type="text" class="form-control my-2" value="<?php echo $order['client_lname']?>" name="last_name" minlength="2" maxlength="50" required>
            <label class="form-label text-muted">Last Name</label>
          </div>
        </div>
        <div class="row mt-3">
          <h4 class="mb-3">Billing Address</h4>
          <div class="col">
            <input type="text" class="form-control my-2" name="street_address" maxlength="255" required>
            <label class="form-label text-muted">Street Address</label>
          </div>
          <div class="col">
            <input type="text" class="form-control my-2" name="city" maxlength="35" required>
            <label class="form-label text-muted">City</label>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col">
            <input type="text" class="form-control is-invalid my-2" name="province" maxlength="35">
            <label class="form-label text-muted">Province</label>
          </div>
          <div class="col">
            <input type="text" class="form-control my-2" value="6000" name="zip" minlength="4" maxlength="4">
            <label class="form-label text-muted">Postal / Zip Code</label>
          </div>
        </div>
        <div class="row my-3">
          <div class="col">
            <h4 class="mb-3">Payment Amount</h4>
            <input type="number" class="form-control my-2" name="amount_paid">
          </div>
          <div class="col">
            <h4 class="mb-3">Payment Method</h4>
              <select class="form-select" name="payment_method">
                <option value="Cash" selected>Cash</option>
                <option value="Check">Check</option>
              </select>
          </div>
        </div>
      <hr class="my-5" />
      <input type="hidden" class="d-none" value="<?php echo $order_id ?>" name="order_id" required>
      <input type="hidden" class="d-none" value="<?php echo $client_id ?>" name="client_id" required>
      <button type="submit" name="log_payment" class="btn btn-primary btn-lg">Log Payment</button>
    </div>
  </form>
</body>
<script>
  var items = Array.from(document.querySelectorAll('input[type=checkbox].form-check-input'));
  var quantity = document.getElementsByClassName('quantity');
  var prices = Array.from(document.querySelectorAll('.prices'));
  var addons = Array.from(document.querySelectorAll('.addons'));
  var amount_due_total = document.getElementById('total');

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

      amount_due_total.innerText = "₱".concat((total.toFixed(2)).toString());
    });
  }

  // text = (price.innerText || textContent).substring(1);
  // parseFloat(price.innerText || )
  // add to total and display
</script>
<script src="../api/bootstrap-bundle-min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> -->
</html>