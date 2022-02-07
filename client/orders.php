<?php
  session_name("sess_id");
  session_start();
  if(!isset($_SESSION['user_id'])){
    Header("Location: ./index.php");
    die();
  } else {
    require_once '../api/api.php';
    $api = new api();
  }

  try {
    $confirmed_referrals = 0;
    $client_id = $_SESSION['client_id'];
    $mysqli_checks = $api->get_workorder($client_id);
    if ($mysqli_checks!==true) {
      throw new Exception('Error: Retrieving client workorder failed.');
    } else {
      $order_id = $_SESSION['order_id'];
    }

    // retrieving workorder details
    $statement = $api->prepare("SELECT order_date, amount_due_total, incentive FROM workorder WHERE order_id=? AND client_id=?");
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

    if(isset($workorder)){
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

      // retrieving referrals
      $statement = $api->prepare("SELECT referral_id, referral_fname, referral_mi, referral_lname, referral_contact_no, referral_email, referral_age, confirmation_status FROM referral WHERE client_id=? AND order_id=?");
      if ($statement===false) {
          throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
      }

      $mysqli_checks = $api->bind_params($statement, "ss", array($client_id, $order_id));
      if ($mysqli_checks===false) {
          throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
      }

      $mysqli_checks = $api->execute($statement);
      if($mysqli_checks===false) {
          throw new Exception('Execute error: The prepared statement could not be executed.');
      }

      $referrals = $api->get_result($statement);
      if($referrals===false){
        throw new Exception('get_result() error: Getting result set from statement failed.');
      }

      $api->free_result($statement);
      $mysqli_checks = $api->close($statement);
      if ($mysqli_checks===false) {
        throw new Exception('The prepared statement could not be closed.');
      } else {
        $statement = null;
      }

      if(strcasecmp($workorder['incentive'], "None") == 0){
        // retrieving confirmed referral count
        $statement = $api->prepare("SELECT referral_id FROM referral WHERE client_id=? AND order_id=? AND confirmation_status=?");
        if ($statement===false) {
            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
        }

        $mysqli_checks = $api->bind_params($statement, "sss", array($client_id, $order_id, "Confirmed"));
        if ($mysqli_checks===false) {
            throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
        }

        $mysqli_checks = $api->execute($statement);
        if($mysqli_checks===false) {
            throw new Exception('Execute error: The prepared statement could not be executed.');
        }

        $res = $api->get_result($statement);
        if($referrals===false){
          throw new Exception('get_result() error: Getting result set from statement failed.');
        }

        $confirmed_referrals = $api->num_rows($res);

        $api->free_result($statement);
        $mysqli_checks = $api->close($statement);
        if ($mysqli_checks===false) {
          throw new Exception('The prepared statement could not be closed.');
        } else {
          $res = null;
          $statement = null;
        }
      }
    }
  } catch (Exception $e) {
    exit();
    $_SESSION['res'] = $e->getMessage();
    Header("Location: ./index.php");
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
  
  <!-- native style -->
  <link href="../style/bootstrap.css" rel="stylesheet">
  <link href="../style/style.css" rel="stylesheet">
  <style>
    .tabs {
      transition: color .4s;
    }

    .tattoo-image {
      max-width: 275px;
      max-height: 275px;
      width: 275px;
      height: 275px;
      background-position: center;
      background-repeat: no-repeat;
      background-size: cover;
    }
  </style>
  <title>Orders | NJC Tattoo</title>
</head>
<body class="w-100">
  <header class="header">
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
  <div class="content w-80">
    <form method="POST" action="../api/queries.php">
      <div>
        <div class="pb-6 border-bottom">
          <h2 class="fw-bold display-3">Orders</h2>
          <p class="d-inline fs-5 text-muted">Manage your ongoing tattoo orders and referrals here. <?php if(isset($workorder)){ echo "Tick the checkboxes of the items you want to modify or remove."; } ?></p>
        </div>
        <?php if(isset($workorder)){ ?>
        <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
          <div>
            <button type="button" class="tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-x-0 border-top-0 text-black" id="orders-tab">Orders</button>
            <button type="button" class="tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-0 text-muted" id="referrals-tab">Referrals</button>
          </div>
          <div>
            <button type="button" id="select-all" class="btn btn-link text-black text-decoration-none me-1">Select All</button>
            <?php if(isset($confirmed_referrals) && $confirmed_referrals >= 3 && strcasecmp($workorder['incentive'], "None") == 0){ ?>
              <button type="button" class="btn btn-primary rounded-pill px-3 py-2 me-1" data-bs-toggle="collapse" data-bs-target="#incentive_form" aria-expanded="true" aria-controls="incentive_form">Avail Incentive</button>
            <?php } ?>
            <?php if($api->num_rows($items) > 0){ ?>
            <div class="d-inline-block" id="order-btn-group">
              <a href="checkout.php" class="btn btn-outline-dark rounded-pill px-3 py-2 me-1">Checkout</a>
              <button type="submit" class="btn btn-outline-primary rounded-pill px-3 py-2 me-1" name="update_items">Update Items</button>
              <button type="submit" class="btn btn-outline-danger rounded-pill px-3 py-2" name="remove_items">Remove Items</button>
            </div>
            <?php } ?>
            <div class="d-none" id="referral-btn-group">
              <button type="button" class="btn btn-outline-primary rounded-pill px-3 py-2 me-1" data-bs-toggle="collapse" data-bs-target="#referral_form" aria-expanded="false" aria-controls="referral_form">Refer Person</button>
              <?php if($api->num_rows($referrals) > 0){ ?>
              <button type="submit" class="btn btn-outline-secondary rounded-pill px-3 py-2 me-1" name="update_referrals">Update Referrals</button>
              <button type="submit" class="btn btn-outline-danger rounded-pill px-3 py-2" name="remove_referrals">Remove Referrals</button>
              <?php } ?>
            </div>
          </div>
        </div>
        <?php } ?>
      </div>
      <?php if(isset($workorder)){ ?>
        <?php if(isset($confirmed_referrals) && $confirmed_referrals >= 3 && strcasecmp($workorder['incentive'], "None") == 0){ ?>
        <div class="collapse border-top-0 border-x-0 rounded mt-3 p-7" id="incentive_form">
          <div class="w-100 d-flex flex-column align-items-start">
            <h2 class="my-4">Avail Workorder Incentive</h2>
              <div class="form-floating w-100 mb-3">
                <select name="incentive" class="form-select" id="incentive">
                  <option value="None"selected>None</option>
                  <option value="15% Discount">15% Discount</option>
                  <option value="Free 3x3 Tattoo">Free 3x3 Tattoo</option>
                </select>
                <label class="form-label" for="incentive">Incentive</label>
              </div>
              <div class="d-none w-100 form-floating mb-3" id="tattoo_selector">
                <select name="tattoo_id" class="form-select">
                  <option value="" hidden selected>Select tattoo</option>
                  <?php
                    try {
                      $statement = $api->prepare("SELECT tattoo_id, tattoo_name, tattoo_image FROM tattoo ORDER BY cataloged DESC");
                      if($statement===false){
                        throw new Exception("prepare() error: The statement could not be prepared.");
                      }
                  
                      $mysqli_checks = $api->execute($statement);
                      if($mysqli_checks===false){
                        throw new Exception('Execute error: The prepared statement could not be executed.');
                      }
                  
                      $res = $api->get_result($statement);
                      if($res===false){
                        throw new Exception('get_result() error: Getting result set from statement failed.');
                      }

                      $api->free_result($statement);
                      $mysqli_checks = $api->close($statement);
                      if ($mysqli_checks===false) {
                        throw new Exception('The prepared statement could not be closed.');
                      }
                    } catch (Exception $e){
                      exit;
                      Header("Location: ./index.php");
                      echo $e->getMessage();
                    }

                    if($api->num_rows($res) > 0){
                      while($row = $api->fetch_assoc($res)){
                        $tattoo_id = $api->clean($row['tattoo_id']);
                        $tattoo_name = $api->clean($row['tattoo_name']);
                        $image = $row['tattoo_image'];
                  ?>
                  <option value="<?php echo $tattoo_id ?>"><?php echo $tattoo_name ?></option>
                  <?php }} ?>
                </select>
                <label class="form-label" for="tattoo_selector">Select your free 3x3 tattoo</label>
              </div>
              <button type="submit" class="d-inline-block w-auto btn btn-primary rounded-pill px-3 py-2" name="avail_incentive">Set Workorder Incentive</button>
          </div>
          <script>
            var incentive = document.getElementById('incentive');
            var select_tattoo = document.getElementById('tattoo_selector');

            incentive.addEventListener('change', function(){
              if(incentive.value.localeCompare("Free 3x3 Tattoo") == 0){
                select_tattoo.classList.remove('d-none');
                select_tattoo.classList.add('d-block');
              } else {
                select_tattoo.classList.remove('d-block');
                select_tattoo.classList.add('d-none');
              }
            });
          </script>
        </div>
        <?php } ?>
        <div class="d-block" id="orders">
          <?php
            if($api->num_rows($items) > 0){
              while($item = $api->fetch_assoc($items)){
          ?>
          <div class="border-bottom d-flex align-items-center justify-content-between p-5">
            <div class="d-flex align-items-center">
              <?php if((strcasecmp($item['item_status'], "Standing") == 0 && strcasecmp($item['paid'], "Unpaid") == 0)){ ?>
                <!-- checkbox -->
                <div class="me-4">
                  <input type="hidden" class="d-none" name="index[]" value="<?php echo $item['item_id']?>" />
                  <input type="checkbox" class="form-check-input p-2 border-dark" name="item[]" value="<?php echo $item['item_id']?>"/>
                </div>
              <?php } ?>
              <!-- tattoo image -->
              <div class="tattoo-image rounded-pill shadow-sm" style="background-image: url(<?php echo $api->clean($item['tattoo_image']); ?>)"></div>
            </div>
            <div class="w-100 ms-6">
              <div class="row my-5">
                <!-- tattoo name -->
                <div class="col">
                  <label class="form-label fw-semibold">Item</label>
                  <p><?php echo $api->clean($item['tattoo_name']) ?></p>
                </div>
                <!-- item status -->
                <div class="col">
                  <label for="status" class="form-label fw-semibold">Item Status</label>
                  <p><?php echo $api->clean($item['item_status']) ?></p>
                  <?php if((strcasecmp($item['item_status'], "Standing") == 0 && strcasecmp($item['paid'], "Unpaid") == 0)){ ?>
                    <input type="hidden" class="d-none" name="status[]" value="<?php echo $item['item_status']?>" />
                  <?php } ?>
                </div>
                <!-- amount_addon -->
                <div class="col">
                  <label for="status" class="form-label fw-semibold">Amount Addon</label>
                  <p><?php echo ($item['amount_addon'] == 0) ? "N/A" : "₱" . $api->clean($item['amount_addon']); ?></p>
                </div>
                <!-- payment status -->
                <div class="col">
                  <label class="form-label fw-semibold">Payment Status</label>
                  <p><?php echo $api->clean($item['paid']); ?></p>
                  <?php if((strcasecmp($item['item_status'], "Standing") == 0 && strcasecmp($item['paid'], "Unpaid") == 0)){ ?>
                    <input type="hidden" class="d-none" name="paid[]" value="<?php echo $item['paid']?>" />
                  <?php } ?>
                </div>
              </div>
              <div class="row my-5">
                <!-- price -->
                <div class="col">
                  <label for="quantity" class="form-label fw-semibold">Price</label>
                  <p>₱<?php echo $api->clean($item['tattoo_price']) ?></p>
                </div>
                <!-- quantity -->
                <div class="col">
                  <label for="quantity" class="form-label fw-semibold">Quantity</label>
                  <?php if((strcasecmp($item['item_status'], "Standing") == 0 && strcasecmp($item['paid'], "Unpaid") == 0)){ ?>
                    <input type="number" class="form-control" value="<?php echo $api->clean($item['tattoo_quantity']) ?>" min="1" name="quantity[]" />
                  <?php } else { ?>
                    <p><?php echo $api->clean($item['tattoo_quantity']) ?></p>
                  <?php } ?>
                </div>
                <!-- width -->
                <div class="col">
                  <label for="width" class="form-label fw-semibold">Width</label>
                  <?php if((strcasecmp($item['item_status'], "Standing") == 0 && strcasecmp($item['paid'], "Unpaid") == 0)){ ?>
                    <input type="number" class="form-control" value="<?php echo $api->clean($item['tattoo_width']) ?>" min="1" name="width[]" />
                  <?php } else { ?>
                    <p><?php echo $api->clean($item['tattoo_width']) ?></p>
                  <?php } ?>
                </div>
                <!-- height --->
                <div class="col">
                  <label for="height" class="form-label fw-semibold">Height</label>
                  <?php if((strcasecmp($item['item_status'], "Standing") == 0 && strcasecmp($item['paid'], "Unpaid") == 0)){ ?>
                    <input type="number" class="form-control" value="<?php echo $api->clean($item['tattoo_height']) ?>" min="1" name="height[]" />
                  <?php } else { ?>
                    <p><?php echo $api->clean($item['tattoo_height']) ?></p>
                  <?php } ?>
                </div>
              </div>
            </div>
          </div>
          <?php
              }
            }
          ?>
        </div>
        <div class="d-none" id="referrals">
          <div class="collapse border-top-0 border-x-0 rounded mt-3 p-7" id="referral_form">
            <div class="row my-4">
              <h2 class="mb-4">Referral Info</h2>
              <div class="col">
                <label class="form-label text-muted" for="first_name">First Name</label>
                <input type="text" class="form-control" name="first_name" minlength="2" maxlength="50">
                <p class="my-2 <?php echo isset($_SESSION['first_name_err']) ? "d-block" : "d-none"; ?> text-danger height_err"><?php if(isset($_SESSION['first_name_err'])){ echo $_SESSION['first_name_err']; } ?></p>
              </div>
              <div class="col-md-2">
                <label class="form-label text-muted" for="mi">Middle Initial</label>
                <input type="text" class="form-control" name="mi" minlength="1" maxlength="1">
                <p class="my-2 <?php echo isset($_SESSION['last_name_err']) ? "d-block" : "d-none"; ?> text-danger height_err"><?php if(isset($_SESSION['last_name_err'])){ echo $_SESSION['last_name_err']; } ?></p>
              </div>
              <div class="col">
                <label class="form-label text-muted" for="last_name">Last Name</label>
                <input type="text" class="form-control" name="last_name" minlength="2" maxlength="50">
                <p class="my-2 <?php echo isset($_SESSION['last_name_err']) ? "d-block" : "d-none"; ?> text-danger height_err"><?php if(isset($_SESSION['last_name_err'])){ echo $_SESSION['last_name_err']; } ?></p>
              </div>
            </div>
            <div class="row my-4">
              <div class="col-md-1">
                <label class="form-label text-muted" for="street_address">Age</label>
                <input type="number" class="form-control" name="age" min="17" max="90">
                <p class="my-2 <?php echo isset($_SESSION['street_address_err']) ? "d-block" : "d-none"; ?> text-danger height_err"><?php if(isset($_SESSION['street_address_err'])){ echo $_SESSION['street_address_err']; } ?></p>
              </div>
              <div class="col">
                <label class="form-label text-muted" for="city">Email Address</label>
                <input type="email" class="form-control" name="email" maxlength="62">
                <p class="my-2 <?php echo isset($_SESSION['city_err']) ? "d-block" : "d-none"; ?> text-danger height_err"><?php if(isset($_SESSION['city_err'])){ echo $_SESSION['city_err']; } ?></p>
              </div>
              <div class="col">
                <label class="form-label text-muted" for="city">Contact Number</label>
                <input type="text" inputmode="numeric" class="form-control" name="contact_number" minlength="7" maxlength="11">
                <p class="my-2 <?php echo isset($_SESSION['city_err']) ? "d-block" : "d-none"; ?> text-danger height_err"><?php if(isset($_SESSION['city_err'])){ echo $_SESSION['city_err']; } ?></p>
              </div>
            </div>
            <div class="mt-5 mb-3">
              <p class="my-2 <?php echo isset($_SESSION['referral_err']) ? "d-block" : "d-none"; ?> text-danger"><?php echo $_SESSION['referral_err']; ?></p>
              <button type="submit" class="w-auto btn btn-primary rounded-pill px-3 py-2" name="refer">Make Referral</button>
            </div>
          </div>
          <?php
            if($api->num_rows($referrals) > 0){
              while($referral = $api->fetch_assoc($referrals)){
          ?>
            <div class="d-flex justify-content-between align-items-center py-4 border-bottom">
              <div class="ms-3">
                <input type="hidden" class="d-none" name="referral_index[]" value="<?php echo $referral['referral_id']?>" />
                <input type="checkbox" class="form-check-input p-2 border-dark" name="referral[]" value="<?php echo $referral['referral_id']?>"/>
              </div>
              <div>
                <label for="tattoo_width">Status</label>
                <p class="my-0 fw-semibold <?php echo strcasecmp($referral['confirmation_status'], "Confirmed") == 0 ? "text-success" : "text-secondary"; ?>"><?php echo $referral['confirmation_status']?></p>
              </div>
              <div>
                <div class="form-floating">
                  <input type="text" class="form-control" value="<?php echo $referral['referral_fname']?>" maxlength="50" placeholder="First Name" name="referral_fname[]" required>
                  <label for="tattoo_width">First Name</label>
                  <p class="d-none my-2 text-danger"></p>
                </div>
              </div>
              <div>
                <div class="form-floating">
                  <input type="text" class="form-control"  style="width: 50px;" value="<?php echo $referral['referral_mi']?>" minlength="1" maxlength="1" placeholder="MI" name="referral_mi[]" required>
                  <label for="tattoo_width">M.I.</label>
                  <p class="d-none my-2 text-danger"></p>
                </div>
              </div>
              <div>
                <div class="form-floating">
                  <input type="text" class="form-control" value="<?php echo $referral['referral_lname']?>" maxlength="50" placeholder="Last Name" name="referral_lname[]" required>
                  <label for="tattoo_width">Last Name</label>
                  <p class="d-none my-2 text-danger"></p>
                </div>
              </div>
              <div>
                <div class="form-floating">
                  <input type="number" class="form-control" style="width: 60px;" value="<?php echo $referral['referral_age']?>" name="referral_age[]" min="17" max="90" required>
                  <label for="tattoo_width">Age</label>
                  <p class="d-none my-2 text-danger"></p>
                </div>
              </div>
              <div>
                <div class="form-floating">
                  <input type="text" class="form-control" value="<?php echo $referral['referral_contact_no']?>" minlength="7" maxlength="11" placeholder="Contact Number" name="referral_contact_no[]">
                  <label for="tattoo_width">Contact Number</label>
                  <p class="d-none my-2 text-danger"></p>
                </div>
              </div>
              <div>
                <div class="form-floating">
                  <input type="email" class="form-control" value="<?php echo $referral['referral_email']?>" maxlength="62" placeholder="Email" name="referral_email[]" required>
                  <label for="tattoo_width">Email</label>
                  <p class="d-none my-2 text-danger"></p>
                </div>
              </div>
            </div>
          <?php
              }
            } else {
          ?>
          <div class="w-100 p-7 border-bottom">
            <h1 class="display-5 fst-italic text-muted">Currently no referrals made for your workorder.</h1>
          </div>
          <?php } ?>
        </div>
        <div class="row mx-0 mb-5 p-5">
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
          <!-- incentive -->
          <div class="col">
            <label class="form-label fw-semibold">Incentive</label>
            <p><?php echo $api->clean($workorder['incentive']); ?></p>
          </div>
          <!-- amount due total -->
          <div class="col">
            <label for="status" class="form-label fw-semibold">Amount Due Total</label>
            <p>Php <?php echo $api->clean($workorder['amount_due_total']) ?></p>
          </div>
        </div>
      <?php } else { ?>
      <div class="w-100 p-7">
        <h1 class="display-2 fst-italic text-muted no-select">You currently have no standing workorder.</h1>
      </div>
      <?php } ?>
    </form>
  </div>
</body>
<?php if(isset($workorder)){ ?>
<script>
  var all_selected = false;
  var select_all = document.getElementById('select-all');
  var checkboxes = document.getElementsByClassName('form-check-input');

  select_all.addEventListener('mouseover', function() {
    this.classList.remove('text-decoration-none');
  });

  select_all.addEventListener('mouseout', function() {
    this.classList.add('text-decoration-none');
  });

  select_all.addEventListener('click', function() {
    all_selected = !all_selected;
    all_selected ? this.innerText = "Deselect" : this.innerText = "Select All";

    for(var i=0, count=checkboxes.length; i < count; i++){
      checkboxes[i].checked = all_selected;
    }
  });

  // page tabs
  var orders_tab = document.getElementById('orders-tab');
  var orders = document.getElementById('orders');
  var order_btn_group = document.getElementById('order-btn-group');

  var referrals_tab = document.getElementById('referrals-tab');
  var referrals = document.getElementById('referrals');
  var referral_btn_group = document.getElementById('referral-btn-group');

  referrals_tab.addEventListener('click', function(){
    orders_tab.className = "tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-0 text-muted";
    this.className = "tabs pb-2 mx-2 px-1 bg-none border-dark border-3 fs-5 border-x-0 border-top-0 text-black";

    orders.className = "d-none";
    referrals.className = "d-block";

    order_btn_group.className = "d-none";
    referral_btn_group.className = "d-inline-block";
  });

  orders_tab.addEventListener('click', function(){
    referrals_tab.className = "tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-0 text-muted";
    this.className = "tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-x-0 border-top-0 text-black";

    orders.className = "d-block";
    referrals.className = "d-none";

    order_btn_group.className = "d-inline-block";
    referral_btn_group.className = "d-none";
  });
</script>
<?php } ?>
<script src="../api/bootstrap-bundle-min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> -->
</html>
<?php
  if(isset($_SESSION['referral_err'])){
    unset($_SESSION['referral_err']);
  }
?>