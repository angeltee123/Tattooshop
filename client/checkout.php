<?php
  session_name("sess_id");
  session_start();
  if(!isset($_SESSION['user_id'])){
    Header("Location: ../client/index.php");
    die();
  } else {
    require_once '../api/api.php';
    $api = new api();
    $client_id = $_SESSION['client_id'];
    $order_id = $_SESSION['order_id'];
  }

  if(!isset($_SESSION['order_id']) || !empty($_SESSION['order_id'])){
    try {
      $left = $api->join("INNER", "order_item", "workorder", "order_item.order_id", "workorder.order_id");
      $right = $api->join("LEFT", $left, "reservation", "order_item.item_id", "reservation.item_id");
      $join = $api->join("INNER", $right, "tattoo", "order_item.tattoo_id", "tattoo.tattoo_id");

      $query = $api->select();
      $query = $api->params($query, array("order_item.item_id", "predecessor_id", "item_status", "paid", "order_item.tattoo_id", "order_item.tattoo_width", "order_item.tattoo_height", "tattoo_name", "tattoo_image", "tattoo_quantity", "tattoo_price", "amount_addon"));
      $query = $api->from($query);
      $query = $api->table($query, $join);
      $query = $api->where($query, array("client_id", "workorder.order_id"), array("?", "?"));
      $where = "AND item_status!=? AND paid!=? ";
      $query = $query . $where;
      $query = $api->order($query, array("item_status", "paid"), array("DESC", "DESC"));

      $statement = $api->prepare($query);
      if ($statement===false) {
          throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
      }
  
      $mysqli_checks = $api->bind_params($statement, "ssss", array($client_id, $order_id, "Applied", "Fully Paid"));
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
    } catch (Exception $e) {
      exit();
      $_SESSION['res'] = $e->getMessage();
      Header("Location: ./client/orders.php");
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
  <link href="./style/bootstrap.css" rel="stylesheet">
  <link href="./style/style.css" rel="stylesheet">
  <title>Order Checkout | NJC Tattoo</title>
</head>
<body>
  <header class="header border-bottom border-2">
    <nav class="nav-bar row mx-0">
      <ul class="col my-0" id="nav-links">
        <li><a href="explore.php">Explore</a></li>
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
  <form action="../api/queries.php" class="w-100 my-5" method="post">
    <div class="row g-3 mb-3">
      <h2>Items</h2>
      <h2>Order ID: <?php echo $order_id ?></h2>
      <table class="table w-75 mx-auto">
        <thead class="align-middle" style="height: 4em;">
          <tr>
            <th scope="col"></th>
            <th scope="col">Tattoo</th>
            <th scope="col">Item Status</th>
            <th scope="col">Payment Status</th>
            <th scope="col">Quantity</th>
            <th scope="col">Reservation Addon</th>
            <th scope="col">Tattoo Price</th>
          </tr>
        </thead>
        <tbody>
          <?php
            // $amount_total = (double) 0.00;

            if($api->num_rows($res) > 0){
              while($row = $api->fetch_assoc($res)){
                if(strcasecmp($row['item_status'], 'Standing') == 0 && strcasecmp($row['paid'], 'Partially Paid') != 0){
                // if(strcasecmp($row['paid'], 'Unpaid') == 0){
                //   $amount_total += ($row['tattoo_price'] * $row['tattoo_quantity']) + $row['amount_addon'];
                // } else if(strcasecmp($row['paid'], 'Partially Paid') == 0){
                //   $amount_total += $row['amount_addon'];
                // }
          ?>
          <tr class="align-middle" style="height: 300px;">
            <td>
              <input type="checkbox" class="form-check-input" value="<?php echo $row['item_id'] ?>" name="item[]">
              <input type="hidden" class="d-none" value="<?php echo $row['item_id'] ?>" name="index[]" >
              <input type="hidden" class="d-none" value="<?php if(!empty($row['predecessor_id'])) { echo $row['predecessor_id']; } else { echo "null"; } ?>" name="predecessor[]" >
            </td>
            <td>
              <div class="d-flex flex-row align-items-center">
                <input type="hidden" class="d-none" value="<?php echo $row['tattoo_id'] ?>" name="tattoo_id[]" >
                <input type="hidden" class="d-none" value="<?php echo $row['tattoo_width'] ?>" name="width[]" >
                <input type="hidden" class="d-none" value="<?php echo $row['tattoo_height'] ?>" name="height[]" >
                <div class="tattoo-image border-2 rounded" style="background-image: url(<?php echo $api->clean($row['tattoo_image']); ?>)"></div>
                <div class="w-auto mx-3">
                  <p class="fw-bold d-inline w-auto"><?php echo $api->clean($row['tattoo_width']) . "x" . $api->clean($row['tattoo_height']) . " " . $api->clean($row['tattoo_name']); ?></p>
                </div>
              </div>
            </td>
            <td>
              <?php echo $api->clean($row['item_status']) ?>
              <input type="hidden" class="d-none" value="<?php echo $row['item_status'] ?>" name="status[]" >
            </td>
            <td>
              <?php echo $api->clean($row['paid']) ?>
              <input type="hidden" class="d-none" value="<?php echo $row['paid'] ?>" name="paid[]" >
            </td>
            <td>
              <input type="hidden" class="d-none" value="<?php echo $row['tattoo_quantity'] ?>" name="quantity[]" >
              <input type="number" class="form-control" value="<?php echo $row['tattoo_quantity'] ?>" min="1" max="<?php echo $row['tattoo_quantity'] ?>" name="checkout_quantity[]" >
            </td>
            <td>
              <?php if(empty($row['amount_addon'])) { echo "N/A"; } else { echo "Php " . number_format($row['amount_addon'], 2, '.', ''); } ?>
              <input type="hidden" class="d-none" value="<?php if(empty($row['amount_addon'])) { echo 0.00 ; } else { echo "Php " . number_format($row['amount_addon'], 2, '.', ''); } ?>" name="addon[]" >
            </td>
            <td>
              Php <?php echo number_format($row['tattoo_price'], 2, '.', '') ?>
              <input type="hidden" class="d-none" value="<?php echo $row['tattoo_price'] ?>" name="price[]" >
            </td>
          </tr>
          <?php }} ?>
          <tfoot style="height: 4em;">
            <td colspan="5"></td>
            <td class="fw-bold">Amount Due Total</td>
            <td id="total">Php 0.00</td>
          </tfoot>
          <?php } else { ?>
            <tfoot>
              <td colspan="5" class="p-5"><h1 class="m-3 display-4 fst-italic text-muted">No tattoos ordered.</h1></td>
            </tfoot>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <div class="mx-auto w-65">
      <div class="row mx-auto w-100 my-5">
        <div class="row my-3">
          <h4 class="mb-3">Client Name</h4>
          <div class="col">
            <input type="text" class="form-control my-2" name="first_name" minlength="2" maxlength="50" required>
            <label class="form-label text-muted">First Name</label>
          </div>
          <div class="col">
            <input type="text" class="form-control my-2" name="last_name" minlength="2" maxlength="50" required>
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
            <input type="text" class="form-control my-2" name="province" maxlength="35" required>
            <label class="form-label text-muted">Province</label>
          </div>
          <div class="col">
            <input type="text" class="form-control my-2" value="6000" name="zip" minlength="4" maxlength="4" required>
            <label class="form-label text-muted">Postal / Zip Code</label>
          </div>
        </div>
        <div class="row my-3">
          <div class="col">
            <h4 class="mb-3">Payment Amount</h4>
            <input type="number" class="form-control my-2" name="payment_amount" required>
          </div>
          <div class="col">
            <h4 class="mb-3">Payment Method</h4>
            <div class="form-check form-check-inline mt-2">
              <input class="form-check-input" type="radio" name="payment_method" value="Debit" required>
              <label class="form-check-label" for="inlineRadio1"><i class="far fa-credit-card"></i>Debit Card</label>
            </div>
            <div class="form-check form-check-inline mt-2">
              <input class="form-check-input" type="radio" name="payment_method" value="Credit" required>
              <label class="form-check-label" for="inlineRadio2"><i class="far fa-credit-card"></i>Credit Card</label>
            </div>
            <div class="form-check form-check-inline mt-2">
              <input class="form-check-input" type="radio" name="payment_method" value="Prepaid" required>
              <label class="form-check-label" for="inlineRadio2"><i class="far fa-credit-card"></i>Prepaid Card</label>
            </div>
          </div>
        </div>
        <div class="row my-3">
          <div class="col">
            <h4 class="mb-3">Card Number</h4>
            <input type="text" inputmode="numeric" class="form-control" name="card_number" minlength="11" maxlength="16" required>
          </div>
          <div class="col">
            <h4 class="mb-3">PIN</h4>
            <input type="password" inputmode="numeric" class="form-control" name="pin" minlength="6" maxlength="6" required>
          </div>
          <div class="col">
            <h4 class="mb-3">Bank Name</h4>
            <input type="text" class="form-control" name="bank_name" required>
          </div>
        </div>
      </div>
      <hr class="my-5" />
      <button type="submit" name="checkout" class="btn btn-primary btn-lg">Checkout</button>
    </div>
  </form>
</body>
<script src="../api/bootstrap-bundle-min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> -->
</html>
<?php
  if(isset($_SESSION['res'])){
    unset($_SESSION['res']);
  }
?>