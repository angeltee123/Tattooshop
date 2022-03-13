<?php
  session_name("sess_id");
  session_start();
  // if(!isset($_SESSION['user_id']) || strcasecmp($_SESSION['user_type'], "User") == 0){
  //   Header("Location: ../client/index.php");
  //   die();
  // } else {
    require_once '../api/api.php';
    $api = new api();
  // }

  try {
    $join = $api->join("INNER", "workorder", "client", "workorder.client_id", "client.client_id");

    $query = $api->select();
    $query = $api->params($query, array("workorder.client_id", "client_fname", "client_lname", "order_id", "order_date", "amount_due_total", "incentive"));
    $query = $api->from($query);
    $query = $api->table($query, $join);
    $query = $api->where($query, "status", "?");
    $query = $api->order($query, "order_date", "ASC");

    $statement = $api->prepare($query);
    if ($statement===false) {
        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
    }

    $mysqli_checks = $api->bind_params($statement, "s", "Ongoing");
    if ($mysqli_checks===false) {
        throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
    }

    $mysqli_checks = $api->execute($statement);
    if($mysqli_checks===false) {
        throw new Exception('Execute error: The prepared statement could not be executed.');
    }

    $orders = $api->get_result($statement);
    if($orders===false){
      throw new Exception('get_result() error: Getting result set from statement failed.');
    }

    $api->free_result($statement);
    $mysqli_checks = $api->close($statement);
    if ($mysqli_checks===false) {
      throw new Exception('The prepared statement could not be closed.');
    } else {
      $statement = null;
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

    .order {
      transition: margin .5s;
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
  <div class="content w-80">
    <div class="pb-6 border-bottom">
      <h2 class="fw-bold display-3">Orders</h2>
      <p class="d-inline fs-5 text-muted">Manage all your ongoing orders and client referrals here. <?php if(isset($workorder)){ echo "Tick the checkboxes of the items you want to modify or remove."; } ?></p>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
      <div>
        <button type="button" class="tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-x-0 border-top-0 text-black" id="all-items-tab">All items</button>
        <button type="button" class="tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-0 text-muted" id="orders-tab">Orders only</button>
        <button type="button" class="tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-0 text-muted" id="referrals-tab">Referrals only</button>
      </div>
      <div>
        <button type="button" class="pb-2 bg-none fs-5 border-0" id="toggle_orders">Show All Orders</button>
      </div>
    </div>
    <div class="vstack">
      <?php
        if($api->num_rows($orders) > 0){
          while($order = $api->fetch_assoc($orders)){
            $client_id = $api->sanitize_data($order['client_id'], "string");
            $client_fname = $api->sanitize_data($order['client_fname'], "string");
            $client_lname = $api->sanitize_data($order['client_lname'], "string");
            $order_id = $api->sanitize_data($order['order_id'], "string");
            $total = number_format($api->sanitize_data($order['amount_due_total'], "float"), 2, '.', '');
            $incentive = $api->sanitize_data($order['incentive'], "string");
      ?>
      <div class="my-2 order">
        <div class="collapsible d-flex align-items-center justify-content-between border rounded-pill">
          <button type="button" class="text-start bg-none border-0 col" data-bs-toggle="collapse" data-bs-target="#order_<?php echo $order_id; ?>" aria-expanded="false" aria-controls="order_<?php echo $order_id; ?>" style="padding: 3.25rem;">
            <h5 class="d-inline">Order placed by
              <?php
                $datetime = explode('-', $api->sanitize_data($order['order_date'], "string"));
                $timestamp = explode(' ', $api->sanitize_data($order['order_date'], "string"));
                $date = date("M:d:Y", strtotime($timestamp[0]));
                $time = date("g:i A", strtotime($timestamp[1]));
                $date = explode(':', $date);
                echo $client_fname . " " . $client_lname;
              ?>
            </h5>
            <p class="d-inline text-muted"><?php echo " on " . $api->sanitize_data($date[0], "string") . " " . $api->sanitize_data($date[1], "int") . ", " . $api->sanitize_data($date[2], "int"); ?></p>
          </button>
          <form method="POST" action="./checkout.php" class="w-auto mx-3 pe-5">
            <input type="hidden" readonly class="d-none" value="<?php echo $client_id; ?>" name="client_id" />
            <input type="hidden" readonly class="d-none" value="<?php echo $order_id; ?>" name="order_id" />
            <button type="submit" class="btn btn-outline-dark rounded-pill d-flex align-items-center me-1" name="order_checkout"><span class="material-icons lh-base pe-2">shopping_cart_checkout</span>Log Payment</button>
          </form>
        </div>
        <div class="collapse rounded reservation mt-3 order_collapsible" id="order_<?php echo $order_id; ?>">
          <div class="row mx-0 p-5">
            <!-- order id -->
            <div class="col">
              <label class="form-label fw-semibold">Order ID</label>
              <p><?php echo $order_id; ?></p>
            </div>
            <!-- order datetime -->
            <div class="col">
              <label class="form-label fw-semibold">Placed on</label>
              <p><?php echo $api->sanitize_data($date[0], "string") . " " . $api->sanitize_data($date[1], "int") . ", " . $api->sanitize_data($date[2], "int") . ", " . $api->sanitize_data($time, "string"); ?></p>
            </div>
            <!-- amount due total -->
            <div class="col">
              <label class="form-label fw-semibold">Amount Due Total</label>
              <p>Php <?php echo $total; ?></p>
            </div>
            <!-- incentive -->
            <div class="col">
              <label class="form-label fw-semibold">Incentive</label>
              <p><?php echo $incentive; ?></p>
            </div>
          </div>
          <div class="items">
            <?php
              try {    
                $retrieve_items = $api->prepare("SELECT order_item.item_id, tattoo_name, tattoo_image, tattoo_price, tattoo_quantity, order_item.tattoo_width, order_item.tattoo_height, paid, item_status, amount_addon FROM ((order_item INNER JOIN tattoo ON order_item.tattoo_id=tattoo.tattoo_id) LEFT JOIN reservation ON order_item.item_id=reservation.item_id) WHERE order_id=? ORDER BY paid ASC, item_status DESC");
                if ($retrieve_items===false) {
                  throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }
      
                $mysqli_checks = $api->bind_params($retrieve_items, "s", $order_id);
                if ($mysqli_checks===false) {
                  throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                }
      
                $mysqli_checks = $api->execute($retrieve_items);
                if($mysqli_checks===false) {
                  throw new Exception('Execute error: The prepared statement could not be executed.');
                }
      
                $items = $api->get_result($retrieve_items);
                if($items===false){
                  throw new Exception('get_result() error: Getting result set from statement failed.');
                }

                $api->free_result($retrieve_items);
                $mysqli_checks = $api->close($retrieve_items);
                if ($mysqli_checks===false) {
                  throw new Exception('The prepared statement could not be closed.');
                }
              } catch (Exception $e) {
                  exit();
                  $_SESSION['res'] = $e->getMessage();
                  Header("Location: ./index.php");
              }

              if($api->num_rows($items) > 0){
                while($item = $api->fetch_assoc($items)){
                  $item_id = $api->sanitize_data($item['item_id'], "string");
                  $tattoo_name = $api->sanitize_data($item['tattoo_name'], "string");
                  $tattoo_image = $api->sanitize_data($item['tattoo_image'], "string");
                  $price = number_format($api->sanitize_data($item['tattoo_price'], "float"), 2, '.', '');
                  $quantity = $api->sanitize_data($item['tattoo_quantity'], "int");
                  $width = $api->sanitize_data($item['tattoo_width'], "int");
                  $height = $api->sanitize_data($item['tattoo_height'], "int");
                  $paid = $api->sanitize_data($item['paid'], "string");
                  $item_status = $api->sanitize_data($item['item_status'], "string");
                  $addon = number_format($api->sanitize_data($item['amount_addon'], "float"), 2, '.', '');
            ?>
            <<?php if((strcasecmp($item_status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)) { ?>form action="./queries.php" method="POST"<?php } else { ?>div<?php } ?> class="border-top d-flex align-items-center justify-content-between p-5">
              <div>
                <div class="tattoo-image rounded-pill shadow-sm" style="background-image: url(<?php echo $tattoo_image; ?>)"></div>
              </div>
              <div class="w-100 ms-6">
                <div class="row my-5">
                  <!-- tattoo name -->
                  <div class="col">
                    <label class="form-label fw-semibold">Item</label>
                    <p><?php echo $tattoo_name; ?></p>
                    <input type="hidden" readonly class="d-none" value="<?php echo $item_id; ?>" name="item_id" />
                  </div>
                  <!-- item status -->
                  <div class="col">
                    <label class="form-label fw-semibold">Item Status</label>
                    <p><?php echo $item_status; ?></p>
                    <?php if((strcasecmp($item_status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)){ ?>
                      <input type="hidden" class="d-none" name="status[]" value="<?php echo $item_status; ?>" />
                    <?php } ?>
                  </div>
                  <!-- amount_addon -->
                  <div class="col">
                    <label class="form-label fw-semibold">Amount Addon</label>
                    <p><?php echo ($addon == 0) ? "N/A" : "₱" . $addon; ?></p>
                  </div>
                  <!-- payment status -->
                  <div class="col">
                    <label class="form-label fw-semibold">Payment Status</label>
                    <p><?php echo $paid; ?></p>
                    <?php if((strcasecmp($item_status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)){ ?>
                      <input type="hidden" class="d-none" name="paid[]" value="<?php echo $paid; ?>" />
                    <?php } ?>
                  </div>
                </div>
                <div class="row my-5">
                  <!-- price -->
                  <div class="col">
                    <label class="form-label fw-semibold">Price</label>
                    <p>₱<?php echo $price; ?></p>
                  </div>
                  <!-- quantity -->
                  <div class="col">
                    <label for="quantity" class="form-label fw-semibold">Quantity</label>
                    <?php if((strcasecmp($item_status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)){ ?>
                      <input type="number" class="form-control" value="<?php echo $quantity; ?>" min="1" name="quantity" />
                    <?php } else { ?>
                      <p><?php echo $quantity; ?></p>
                    <?php } ?>
                  </div>
                  <!-- width -->
                  <div class="col">
                    <label for="width" class="form-label fw-semibold">Width</label>
                    <?php if((strcasecmp($item_status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)){ ?>
                      <input type="number" class="form-control" value="<?php echo $width; ?>" min="1" name="width" />
                    <?php } else { ?>
                      <p><?php echo $width; ?></p>
                    <?php } ?>
                  </div>
                  <!-- height --->
                  <div class="col">
                    <label for="height" class="form-label fw-semibold">Height</label>
                    <?php if((strcasecmp($item_status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)){ ?>
                      <input type="number" class="form-control" value="<?php echo $tattoo_height; ?>" min="1" name="height" />
                    <?php } else { ?>
                      <p><?php echo $height; ?></p>
                    <?php } ?>
                  </div>
                </div>
                <?php if((strcasecmp($item_status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)){ ?>
                  <div class="w-100">
                    <div class="float-end">
                      <button type="submit" class="me-1 btn btn-outline-primary" name="update_item">Update</button>
                      <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#delete_<?php echo $item_id; ?>">Remove Item</button>
                    </div>
                  </div>
                <?php } ?>
              </div>
            </<?php echo ((strcasecmp($item_status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)) ? "form" : "div"; ?>>
            </form>
            <?php
              // deletion modal
              if((strcasecmp($item_status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)){ ?>
              <div class="modal fade" id="delete_<?php echo $item_id; ?>" tabindex="-1" aria-labelledby="delete_<?php echo $item_id; ?>_label" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="#delete_<?php echo $item_id; ?>_label">Confirm Removal</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <p>Are you sure you want to remove this item from the workorder?</p>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <form action="./queries.php" method="POST">
                        <input type="hidden" class="d-none" name="item_id" value="<?php echo $item_id; ?>">
                        <button type="submit" class="btn btn-outline-danger" name="delete_item">Yes, remove it</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            <?php } ?>
          <?php
              }
          ?>
            </div>
          <?php
            } else { ?>
              <div class="p-5"><h1 class="m-3 display-4 fst-italic text-muted">No tattoos ordered.</h1></div>
          <?php } ?>
          <div class="referrals">
          <?php
            try {    
              $retrieve_referrals = $api->prepare("SELECT referral_id, referral_fname, referral_mi, referral_lname, referral_contact_no, referral_email, referral_age, confirmation_status FROM (workorder INNER JOIN referral ON workorder.order_id=referral.order_id) WHERE referral.order_id=? AND referral.client_id=?");
              if ($retrieve_referrals===false) {
                  throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
              }
    
              $mysqli_checks = $api->bind_params($retrieve_referrals, "ss", array($order_id, $client_id));
              if($mysqli_checks===false) {
                  throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
              }
    
              $mysqli_checks = $api->execute($retrieve_referrals);
              if($mysqli_checks===false) {
                  throw new Exception('Execute error: The prepared statement could not be executed.');
              }
    
              $referrals = $api->get_result($retrieve_referrals);
              if($referrals===false){
                throw new Exception('get_result() error: Getting result set from statement failed.');
              }

              $api->free_result($retrieve_referrals);
              $mysqli_checks = $api->close($retrieve_referrals);
              if ($mysqli_checks===false) {
                throw new Exception('The prepared statement could not be closed.');
              }
            } catch (Exception $e) {
                exit();
                $_SESSION['res'] = $e->getMessage();
                Header("Location: ./index.php");
            }

            if($api->num_rows($referrals) > 0){
              while($referral = $api->fetch_assoc($referrals)){
                $referral_id = $api->sanitize_data($referral['referral_id'], "string");
                $first_name = $api->sanitize_data($referral['referral_fname'], "string");
                $mi = $api->sanitize_data($referral['referral_mi'], "string");
                $last_name = $api->sanitize_data($referral['referral_lname'], "string");
                $contact_no = $api->sanitize_data($referral['referral_contact_no'], "int");
                $email = $api->sanitize_data($referral['referral_email'], "email");
                $age = $api->sanitize_data($referral['referral_age'], "int");
                $confirmation_status = $api->sanitize_data($referral['confirmation_status'], "string");
            ?>
            <form method="POST" action="./queries.php" class="d-flex justify-content-between align-items-center py-4 border-top">
              <div>
                <input type="hidden" class="d-none" name="referral_id" value="<?php echo $referral_id; ?>" />
                <?php if(in_array($confirmation_status, array("Confirmed", "Declined"))){ ?>
                  <label class="fw-semibold">Status</label>   
                  <p class="my-0 fw-semibold <?php echo strcasecmp($confirmation_status, "Confirmed") == 0 ? "text-success" : "text-secondary"; ?>"><?php echo $confirmation_status; ?></p>
                <?php } else { ?>
                  <div class="form-floating">
                    <select name="confirmation_status" class="form-select">
                      <option value="Pending" selected>Pending</option>
                      <option value="Confirmed">Confirmed</option>
                      <option value="Declined">Declined</option>
                    </select>
                    <label for="confirmation_status">Status</label>   
                    <p class="d-none my-2 text-danger"></p>                  
                  </div>
                <?php } ?>
              </div>
              <div>
                <?php if(in_array($confirmation_status, array("Confirmed", "Declined"))){ ?>
                  <label class="fw-semibold">First Name</label>   
                  <p class="my-0"><?php echo $first_name; ?></p>
                <?php } else { ?>
                  <div class="form-floating">
                    <input type="text" class="form-control" value="<?php echo $first_name; ?>" maxlength="50" placeholder="First Name" name="referral_fname" required>
                    <label for="referral_fname">First Name</label>
                    <p class="d-none my-2 text-danger"></p>
                  </div>
                <?php } ?> 
              </div>
              <div>
                <?php if(in_array($confirmation_status, array("Confirmed", "Declined"))){ ?>
                  <label class="fw-semibold">Middle Initial</label>   
                  <p class="my-0"><?php echo $mi; ?>.</p>
                <?php } else { ?>
                  <div class="form-floating">
                    <input type="text" class="form-control" style="width: 50px;" value="<?php echo $mi; ?>" minlength="1" maxlength="1" placeholder="MI" name="referral_mi" required>
                    <label for="referral_mi">M.I.</label>
                    <p class="d-none my-2 text-danger"></p>
                  </div>
                <?php } ?> 
              </div>
              <div>
                <?php if(in_array($confirmation_status, array("Confirmed", "Declined"))){ ?>
                  <label class="fw-semibold">Last Name</label>   
                  <p class="my-0"><?php echo $last_name; ?></p>
                <?php } else { ?>
                  <div class="form-floating">
                    <input type="text" class="form-control" value="<?php echo $last_name; ?>" maxlength="50" placeholder="Last Name" name="referral_lname" required>
                    <label for="referral_lname">Last Name</label>
                    <p class="d-none my-2 text-danger"></p>
                  </div>
                <?php } ?>
              </div>
              <div>
                <?php if(in_array($confirmation_status, array("Confirmed", "Declined"))){ ?>
                  <label class="fw-semibold">Age</label>   
                  <p class="my-0"><?php echo $age; ?></p>
                <?php } else { ?>
                  <div class="form-floating">
                    <input type="number" class="form-control" style="width: 60px;" value="<?php echo $age; ?>" name="referral_age" min="17" max="90" required>
                    <label for="referral_age">Age</label>
                    <p class="d-none my-2 text-danger"></p>
                  </div>
                <?php } ?>
              </div>
              <div>
                <?php if(in_array($confirmation_status, array("Confirmed", "Declined"))){ ?>
                  <label class="fw-semibold">Contact Number</label>   
                  <p class="my-0"><?php echo $contact_no; ?></p>
                <?php } else { ?>
                  <div class="form-floating">
                    <input type="text" class="form-control" value="<?php echo $contact_no; ?>" minlength="7" maxlength="11" placeholder="Contact Number" name="referral_contact_no">
                    <label for="referral_contact_no">Contact Number</label>
                    <p class="d-none my-2 text-danger"></p>
                  </div>
                <?php } ?>
              </div>
              <div>
                <?php if(in_array($confirmation_status, array("Confirmed", "Declined"))){ ?>
                  <label class="fw-semibold">Email</label>   
                  <p class="my-0"><?php echo $email; ?></p>
                <?php } else { ?>
                  <div class="form-floating">
                    <input type="email" class="form-control" value="<?php echo $email; ?>" maxlength="62" placeholder="Email" name="referral_email" required>
                    <label for="referral_email">Email</label>
                    <p class="d-none my-2 text-danger"></p>
                  </div>
                <?php } ?>
              </div>
              <div>
                <?php if(strcasecmp($confirmation_status, "Pending") == 0){ ?>
                  <div class="d-inline-block">
                    <button type="submit" class="btn btn-outline-primary rounded-pill d-flex align-items-center me-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Update Referral" name="update_referral"><span class="material-icons lh-base">edit</span</button>
                  </div>
                <?php } ?>
                <div class="d-inline-block">
                  <button type="submit" class="btn btn-outline-danger rounded-pill d-flex align-items-center me-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove Referral" name="remove_referral"><span class="material-icons lh-base">person_remove</span></button>
                </div>
              </div>
            </form>
            <?php
                }
              } else {
            ?>
            <h1 class="p-7 border-top display-2 fst-italic text-muted no-select">Currently no referrals made for this order.</h1>
            <?php
              }
            ?>
          </div>
        </div>
      </div>
      <?php
        }
      } else {
      ?>
      <h1 class="p-7 border-top display-2 fst-italic text-muted no-select">No ongoing workorders.</h1>
      <?php
      }
      ?>
    </div>
  </div>  
</body>
<script src="../api/bootstrap-bundle-min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> -->
<script>
  // tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  });

  // collapsibles
  var show_orders = false;
  var toggle_orders = document.getElementById('toggle_orders');
  var orders = document.getElementsByClassName('order');
  var order_collapsibles = document.getElementsByClassName('order_collapsible');

  // tabs
  var all_items_tab = document.getElementById('all-items-tab');
  var orders_tab = document.getElementById('orders-tab');
  var referrals_tab = document.getElementById('referrals-tab');

  // tab sections
  var items = document.getElementsByClassName('items');
  var referrals = document.getElementsByClassName('referrals');
  
  // collapsibles stateful styling
  for(var i=0, count=orders.length; i < count; i++){
    let order = orders[i];

    order_collapsibles[i].addEventListener('shown.bs.collapse', function () {
      order.classList.remove('my-2');
      order.classList.add('my-4');
    });

    order_collapsibles[i].addEventListener('hidden.bs.collapse', function () {
      order.classList.remove('my-4');
      order.classList.add('my-2');
    });
  }

  // toggling all collapsibles
  toggle_orders.addEventListener('click', function() {
    show_orders = !show_orders;
    show_orders === true ? toggle_orders.innerText = "Hide All Orders" : toggle_orders.innerText = "Show All Orders";
    
    for(var i=0, count=order_collapsibles.length; i < count; i++){
      if(show_orders === true){
        if(!(order_collapsibles[i].classList.contains('show'))){
          let collapse = new bootstrap.Collapse(order_collapsibles[i], { show: true, hide: false });
        }
      } else {
        if((order_collapsibles[i].classList.contains('show'))){
          let collapse = new bootstrap.Collapse(order_collapsibles[i], { show: false, hide: true });
        }
      }
    }
  });

  // tabs stateful styling
  all_items_tab.addEventListener('click', function(){
    this.className = "tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-x-0 border-top-0 text-black";
    orders_tab.className = "tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-0 text-muted";
    referrals_tab.className = "tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-0 text-muted";

    for(var i=0, count=items.length; i < count; i++){
      items[i].classList.remove('d-none');
      items[i].classList.add('d-block');
    }

    for(var i=0, count=referrals.length; i < count; i++){
      referrals[i].classList.remove('d-none');
      referrals[i].classList.add('d-block');
    }
  });

  orders_tab.addEventListener('click', function(){
    this.className = "tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-x-0 border-top-0 text-black";
    referrals_tab.className = "tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-0 text-muted";
    all_items_tab.className = "tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-0 text-muted";

    for(var i=0, count=items.length; i < count; i++){
      items[i].classList.remove('d-none');
      items[i].classList.add('d-block');
    }

    for(var i=0, count=referrals.length; i < count; i++){
      referrals[i].classList.remove('d-block');
      referrals[i].classList.add('d-none');
    }
  });

  referrals_tab.addEventListener('click', function(){
    this.className = "tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-x-0 border-top-0 text-black";
    orders_tab.className = "tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-0 text-muted";
    all_items_tab.className = "tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-0 text-muted";

    for(var i=0, count=items.length; i < count; i++){
      items[i].classList.remove('d-block');
      items[i].classList.add('d-none');
    }

    for(var i=0, count=referrals.length; i < count; i++){
      referrals[i].classList.remove('d-none');
      referrals[i].classList.add('d-block');
    }
  });
</script>
</html>
<?php
  if(isset($_SESSION['res'])){
    unset($_SESSION['res']);
  }
?>