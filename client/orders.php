<?php
  session_name("sess_id");
  session_start();
  if(!isset($_SESSION['user']['user_id'])){
    Header("Location: ./index.php");
    die();
  } else {
    require_once '../api/api.php';
    $api = new api();
  }

  try {
    $confirmed_referrals = 0;
    $editable_rows = 0;

    $client_id = $api->sanitize_data($_SESSION['user']['client_id'], 'string');
    
    $mysqli_checks = $api->get_workorder($client_id);
    if($mysqli_checks!==true){
      throw new Exception('Error: Retrieving client workorder failed.');
    } else {
      $order_id = $api->sanitize_data($_SESSION['order']['order_id'], 'string');
      if(!empty($_SESSION['order']['order_id'])){
        $order_id = $api->sanitize_data($_SESSION['order']['order_id'], 'string');
        $order_date = $_SESSION['order']['order_date'];
        $total = number_format($api->sanitize_data($_SESSION['order']['amount_due_total'], "float"), 2, '.', '');
        $incentive = $api->sanitize_data($_SESSION['order']['incentive'], 'string');
      }
    }

    // retrieving order items
    $statement = $api->prepare("SELECT order_item.item_id, tattoo_name, tattoo_image, tattoo_price, tattoo_quantity, order_item.tattoo_width, order_item.tattoo_height, paid, item_status, amount_addon FROM ((order_item INNER JOIN tattoo ON order_item.tattoo_id=tattoo.tattoo_id) LEFT JOIN reservation ON order_item.item_id=reservation.item_id) WHERE order_id=? ORDER BY item_status ASC, paid ASC");
    if($statement===false){
      throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
    }

    $mysqli_checks = $api->bind_params($statement, "s", $order_id);
    if($mysqli_checks===false){
      throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
    }

    $mysqli_checks = $api->execute($statement);
    if($mysqli_checks===false){
      throw new Exception('Execute error: The prepared statement could not be executed.');
    }

    $items = $api->get_result($statement);
    if($items===false){
      throw new Exception('get_result() error: Getting result set from statement failed.');
    }

    $api->free_result($statement);
    $mysqli_checks = $api->close($statement);
    ($mysqli_checks===false) ? throw new Exception('The prepared statement could not be closed.') : $statement = null;

    if(!empty($_SESSION['order']['order_id'])){
      // retrieving editable order item count
      $statement = $api->prepare("SELECT item_id FROM order_item WHERE order_id=? AND paid=? AND item_status=?");
      if($statement===false){
        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
      }

      $mysqli_checks = $api->bind_params($statement, "sss", array($order_id, "Unpaid", "Standing"));
      if($mysqli_checks===false){
        throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
      }

      $mysqli_checks = $api->execute($statement);
      if($mysqli_checks===false){
        throw new Exception('Execute error: The prepared statement could not be executed.');
      }

      $res = $api->get_result($statement);
      if($res===false){
        throw new Exception('get_result() error: Getting result set from statement failed.');
      }

      $editable_rows = $api->num_rows($res);

      $api->free_result($statement);
      $mysqli_checks = $api->close($statement);
      if($mysqli_checks===false){
        throw new Exception('The prepared statement could not be closed.');
      } else {
        $res = null;
        $statement = null;
      }

      // retrieving referrals
      $statement = $api->prepare("SELECT referral_id, referral_fname, referral_mi, referral_lname, referral_contact_no, referral_email, referral_age, confirmation_status FROM referral WHERE client_id=? AND order_id=?");
      if($statement===false){
        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
      }

      $mysqli_checks = $api->bind_params($statement, "ss", array($client_id, $order_id));
      if($mysqli_checks===false){
        throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
      }

      $mysqli_checks = $api->execute($statement);
      if($mysqli_checks===false){
        throw new Exception('Execute error: The prepared statement could not be executed.');
      }

      $referrals = $api->get_result($statement);
      if($referrals===false){
        throw new Exception('get_result() error: Getting result set from statement failed.');
      }

      $api->free_result($statement);
      $mysqli_checks = $api->close($statement);
      ($mysqli_checks===false) ? throw new Exception('The prepared statement could not be closed.') : $statement = null;

      if(strcasecmp($incentive, "None") == 0){
        // retrieving confirmed referral count
        $statement = $api->prepare("SELECT referral_id FROM referral WHERE client_id=? AND order_id=? AND confirmation_status=?");
        if($statement===false){
          throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
        }

        $mysqli_checks = $api->bind_params($statement, "sss", array($client_id, $order_id, "Confirmed"));
        if($mysqli_checks===false){
          throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
        }

        $mysqli_checks = $api->execute($statement);
        if($mysqli_checks===false){
          throw new Exception('Execute error: The prepared statement could not be executed.');
        }

        $res = $api->get_result($statement);
        if($res===false){
          throw new Exception('get_result() error: Getting result set from statement failed.');
        }

        $confirmed_referrals = $api->num_rows($res);

        $api->free_result($statement);
        $mysqli_checks = $api->close($statement);
        if($mysqli_checks===false){
          throw new Exception('The prepared statement could not be closed.');
        } else {
          $res = null;
          $statement = null;
        }
      }
    }
  } catch (Exception $e) {
    $_SESSION['res'] = $e->getMessage();
    Header("Location: ./index.php");
    exit();
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require_once '../common/meta.php'; ?>
  <link href="../style/orders.css" rel="stylesheet">
  <!-- native style -->
  <link href="./style/orders.css" rel="stylesheet">
  <title>Orders | NJC Tattoo</title>
</head>
<body class="w-100">
  <?php require_once '../common/header.php'; ?>
  <div class="Orders content">
    <form id="Orders__form" method="POST" action="../scripts/php/queries.php">
      <div class="Orders__header">
        <h2 class="fw-bold display-3">Orders</h2>
        <p class="d-inline fs-5 text-muted">Manage your ongoing tattoo orders and referrals here. <?php if(!empty($_SESSION['order']['order_id'])){ echo "Tick the checkboxes of the items you want to modify or remove."; } ?></p>
      </div>
      <?php if(!empty($order_id)){ ?>
        <div class="Orders__controls">
          <div>
            <button type="button" class="Orders__controls--tab border-0 border-bottom text-black" id="Orders__controls--tab--orders">Orders</button>
            <button type="button" class="Orders__controls--tab border-0 text-muted" id="Orders__controls--tab--referrals">Referrals</button>
          </div>
          <div class="d-flex align-items-center">
            <?php if($editable_rows > 0){ ?>
              <button type="button" id="Orders__controls__select-all-orders" class="Orders__controls__select-all btn btn-link d-inline-block">Select All</button>
            <?php } if($api->num_rows($referrals) > 0){ ?>
              <button type="button" id="Orders__controls__select-all-referrals" class="Orders__controls__select-all btn btn-link d-none">Select All</button>
            <?php } if(isset($confirmed_referrals) && $confirmed_referrals >= 3 && strcasecmp($incentive, "None") == 0){ ?>
              <button type="button" class="btn btn-primary rounded-pill px-3 py-2 me-1" data-bs-toggle="collapse" data-bs-target="#Orders__incentive-form" aria-expanded="true" aria-controls="Orders__incentive-form">Avail Incentive</button>
            <?php } if($api->num_rows($items) > 0){ ?>
            <div id="Orders__controls--orders" class="Orders__controls__control-group d-block">
              <div>
                <a href="./checkout.php" class="Orders__controls__control btn btn-outline-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Order Checkout"><span class="Orders__controls__control__icon material-icons">shopping_cart_checkout</span><span class="Orders__controls__control__text">Checkout</span></a>
              </div>
              <?php if($editable_rows > 0){ ?>
                <div>
                  <button type="submit" class="Orders__controls__control btn btn-outline-primary ms-1" id="update_items" name="update_items" data-bs-toggle="tooltip" data-bs-placement="top" title="Update Items"><span class="Orders__controls__control__icon material-icons">edit</span><span class="Orders__controls__control__text">Update Items</span></button>
                </div>
                <div>
                  <button type="submit" class="Orders__controls__control btn btn-outline-danger ms-1" name="remove_items" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove Items"><span class="Orders__controls__control__icon material-icons">remove_circle</span><span class="Orders__controls__control__text">Remove Items</span></button>
                </div>
              <?php } ?>
            </div>
            <?php } ?>
            <div id="Orders__controls--referrals" class="Orders__controls__control-group d-none">
              <div>
                <button type="button" class="Orders__controls__control btn btn-outline-primary ms-1" data-bs-toggle="collapse" data-bs-target="#Orders__referral-form" aria-expanded="false" aria-controls="Orders__referral-form"><span class="Orders__controls__control__icon material-icons">person_add</span><span class="Orders__controls__control__text">Refer Person</span></button>
              </div>
              <?php if($api->num_rows($referrals) > 0){ ?>
                <div>
                  <button type="submit" class="Orders__controls__control btn btn-outline-secondary ms-1" id="update_referrals" name="update_referrals" data-bs-toggle="tooltip" data-bs-placement="top" title="Update Referrals"><span class="Orders__controls__control__icon material-icons">person</span><span class="Orders__controls__control__text">Update Referrals</span></button>
                </div>
                <div>
                  <button type="submit" class="Orders__controls__control btn btn-outline-danger ms-1" name="remove_referrals" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove Referrals"><span class="Orders__controls__control__icon material-icons">person_remove</span><span class="Orders__controls__control__text">Remove Referrals</span></button>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>
      <?php } ?>
      <?php if(!empty($order_id)){ ?>
        <?php if(isset($confirmed_referrals) && $confirmed_referrals >= 3 && strcasecmp($workorder['incentive'], "None") == 0){ ?>
        <div class="collapse border-bottom rounded mt-3 p-7" id="Orders__incentive-form">
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
                      if($mysqli_checks===false){
                        throw new Exception('The prepared statement could not be closed.');
                      }
                    } catch (Exception $e) {
                      Header("Location: ./index.php");
                      echo $e->getMessage();
                      exit;
                    }

                    if($api->num_rows($res) > 0){
                      while($row = $api->fetch_assoc($res)){
                        $tattoo_id = $api->sanitize_data($row['tattoo_id'], 'string');
                        $tattoo_name = $api->sanitize_data($row['tattoo_name'], 'string');
                        $image = $api->sanitize_data($row['tattoo_image'], 'string');
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
                select_tattoo.classList.replace('d-none', 'd-block');
              } else {
                select_tattoo.classList.remove('d-block', 'd-none');
              }
            });
          </script>
        </div>
        <?php } ?>
        <div class="d-block" id="Orders__orders">
          <?php
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
                $status = $api->sanitize_data($item['item_status'], "string");
                $addon = number_format($api->sanitize_data($item['amount_addon'], "float"), 2, '.', '');
          ?>
          <div class="Orders__order-item">
            <div class="d-flex align-items-center">
              <?php if((strcasecmp($status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)){ ?>
                <!-- checkbox -->
                <div class="me-4">
                  <input type="hidden" class="d-none" name="index[]" value="<?php echo $item_id; ?>" />
                  <input type="checkbox" class="Orders__order-item__checkbox form-check-input" name="item[]" value="<?php echo $item_id; ?>"/>
                </div>
              <?php } ?>
              <!-- tattoo image -->
              <div class="Orders__order-item__tattoo-preview shadow-sm" style="background-image: url(<?php echo $tattoo_image; ?>)"></div>
            </div>
            <div class="Orders__order-item__details">
              <div class="row">
                <div class="col">
                  <!-- tattoo name -->
                  <label class="form-label fw-semibold">Item</label>
                  <p><?php echo $tattoo_name; ?></p>
                </div>
                <div class="col">
                  <!-- item status -->
                  <label for="status" class="form-label fw-semibold">Item Status</label>
                  <p><?php echo $status; ?></p>
                  <?php if((strcasecmp($status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)){ ?>
                    <input type="hidden" class="d-none" name="status[]" value="<?php echo $status; ?>" />
                  <?php } ?>
                </div>
                <div class="col">
                  <!-- amount_addon -->
                  <label for="status" class="form-label fw-semibold">Amount Addon</label>
                  <p><?php echo ($addon == 0) ? "N/A" : "₱" . $addon; ?></p>
                </div>
                <div class="col">
                  <!-- payment status -->
                  <label class="form-label fw-semibold">Payment Status</label>
                  <p><?php echo $paid; ?></p>
                  <?php if((strcasecmp($status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)){ ?>
                    <input type="hidden" class="d-none" name="paid[]" value="<?php echo $paid; ?>" />
                  <?php } ?>
                </div>
              </div>
              <div class="row">
                <div class="col">
                  <!-- price -->
                  <label for="quantity" class="form-label fw-semibold">Price</label>
                  <p>₱<?php echo $price; ?></p>
                </div>
                <div class="col">
                  <!-- quantity -->
                  <label for="quantity" class="form-label fw-semibold">Quantity</label>
                  <?php if((strcasecmp($status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)){ ?>
                    <input type="number" class="form-control" name="quantity[]" min="1" value="<?php echo $quantity; ?>"/>
                    <label class="error-message quantity_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span><span></span></label>
                  <?php } else { ?>
                    <p><?php echo $quantity; ?></p>
                  <?php } ?>
                </div>
                <div class="col">
                  <!-- width -->
                  <label for="width" class="form-label fw-semibold">Width</label>
                  <?php if((strcasecmp($status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)){ ?>
                    <input type="number" class="form-control" name="width[]" min="1" max="24" value="<?php echo $width; ?>"/>
                    <label class="error-message width_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span><span></span></label>
                  <?php } else { ?>
                    <p><?php echo $width; ?></p>
                  <?php } ?>
                </div>
                <div class="col">
                  <!-- height --->
                  <label for="height" class="form-label fw-semibold">Height</label>
                  <?php if((strcasecmp($status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)){ ?>
                    <input type="number" class="form-control" name="height[]" min="1" max="36" value="<?php echo $height; ?>"/>
                    <label class="error-message height_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span><span></span></label>
                  <?php } else { ?>
                    <p><?php echo $height; ?></p>
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
        <div class="d-none" id="Orders__referrals">
          <div class="collapse border-bottom mt-3 p-7" id="Orders__referral-form">
            <div class="row align-items-start">
              <h2 class="mb-4">Referral Info</h2>
              <div class="Orders__referral-form__col col">
                <label class="form-label text-muted" for="first_name">First Name</label>
                <input type="text" class="form-control<?php if(isset($_SESSION['first_name_err'])) echo " is-invalid"; ?>" name="first_name" id="first_name" minlength="2" maxlength="50">
                <label id="first_name_err" class="error-message <?php echo isset($_SESSION['first_name_err']) ? "d-flex" : "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['first_name_err'])){ echo $_SESSION['first_name_err']; } ?></span></label>
              </div>
              <div class="Orders__referral-form__col col-md-3">
                <label class="form-label text-muted" for="mi">Middle Initial (Optional)</label>
                <input type="text" class="form-control<?php if(isset($_SESSION['mi_err'])) echo " is-invalid"; ?>" name="mi" id="mi" minlength="1" maxlength="2">
                <label id="mi_err" class="error-message <?php echo isset($_SESSION['mi_err']) ? "d-flex" : "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['mi_err'])){ echo $_SESSION['mi_err']; } ?></span></label>
              </div>
              <div class="Orders__referral-form__col col">
                <label class="form-label text-muted" for="last_name">Last Name</label>
                <input type="text" class="form-control<?php if(isset($_SESSION['last_name_err']))echo " is-invalid"; ?>" name="last_name" id="last_name" minlength="2" maxlength="50">
                <label id="last_name_err" class="error-message <?php echo isset($_SESSION['last_name_err']) ? "d-flex" : "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['last_name_err'])){ echo $_SESSION['last_name_err']; } ?></span></label>
              </div>
            </div>
            <div class="row align-items-start">
              <div class="Orders__referral-form__col col-md-2">
                <label class="form-label text-muted" for="age">Age</label>
                <input type="number" class="form-control<?php if(isset($_SESSION['age_err'])) echo " is-invalid"; ?>" name="age" id="age" min="18" max="90">
                <label id="age_err" class="error-message <?php echo isset($_SESSION['age_err']) ? "d-flex" : "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['age_err'])){ echo $_SESSION['age_err']; } ?></span></label>
              </div>
              <div class="Orders__referral-form__col col">
                <label class="form-label text-muted" for="email">Email Address</label>
                <input type="email" class="form-control<?php if(isset($_SESSION['email_err'])) echo " is-invalid"; ?>" name="email" id="email" maxlength="62">
                <label id="email_err" class="error-message <?php echo isset($_SESSION['email_err']) ? "d-flex" : "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['email_err'])){ echo $_SESSION['email_err']; } ?></span></label>
              </div>
              <div class="Orders__referral-form__col col">
                <label class="form-label text-muted" for="contact_number">Contact Number</label>
                <input type="text" inputmode="numeric" class="form-control<?php if(isset($_SESSION['contact_number_err'])) echo " is-invalid"; ?>" name="contact_number" id="contact_number" minlength="7" maxlength="11">
                <label id="contact_number_err" class="error-message <?php echo isset($_SESSION['contact_number_err']) ? "d-flex" : "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['contact_number_err'])){ echo $_SESSION['contact_number_err']; } ?></span></label>
              </div>
            </div>
            <div class="mt-5 mb-3">
              <label class="error-message <?php echo isset($_SESSION['referral_err']) ? "d-flex" : "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><?php if(isset($_SESSION['referral_err'])){ echo $_SESSION['referral_err']; } ?></label>
              <button type="submit" class="w-auto btn btn-primary rounded-pill px-3 py-2" name="refer" id="refer">Make Referral</button>
            </div>
          </div>
          <?php
            if($api->num_rows($referrals) > 0){
              while($referral = $api->fetch_assoc($referrals)){
                $referral_id = $api->sanitize_data($referral['referral_id'], "string");
                $first_name = $api->sanitize_data($referral['referral_fname'], "string");
                $last_name = $api->sanitize_data($referral['referral_lname'], "string");
                $mi = $api->sanitize_data($referral['referral_mi'], "string");
                $contact_no = $api->sanitize_data($referral['referral_contact_no'], "int");
                $email = $api->sanitize_data($referral['referral_email'], "email");
                $age = $api->sanitize_data($referral['referral_age'], "int");
                $status = $api->sanitize_data($referral['confirmation_status'], "string");
          ?>
            <div class="Orders__referral">
              <div class="Orders__referral__input-group">
                <div class="align-self-center ms-3 me-2">
                  <input type="hidden" class="d-none" name="referral_index[]" value="<?php echo $referral_id; ?>" />
                  <input type="checkbox" class="Orders__referral__checkbox form-check-input p-2 border-dark" name="referral[]" value="<?php echo $referral_id; ?>"/>
                </div>
                <div class="align-self-center mx-3">
                  <label for="tattoo_width">Status</label>
                  <p class="my-0 fw-semibold <?php echo strcasecmp($status, "Confirmed") == 0 ? "text-success" : "text-secondary"; ?>"><?php echo $status; ?></p>
                </div>
                <div class="flex-fill mx-2">
                  <div class="form-floating">
                    <input type="text" class="form-control" value="<?php echo $first_name; ?>" maxlength="50" placeholder="First Name" name="referral_fname[]" required />
                    <label for="tattoo_width">First Name</label>
                  </div>
                  <label class="error-message referral_fname_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span><span></span></label>
                </div>
                <div class="mx-2">
                  <div class="form-floating">
                    <input type="text" class="form-control" style="min-width: 75px;" value="<?php echo $mi; ?>" minlength="1" maxlength="1" placeholder="MI" name="referral_mi[]" />
                    <label for="tattoo_width">M.I.</label>
                  </div>
                  <label class="error-message referral_mi_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span><span></span></label>
                </div>
                <div class="flex-fill mx-2">
                  <div class="form-floating">
                    <input type="text" class="form-control" value="<?php echo $last_name; ?>" maxlength="50" placeholder="Last Name" name="referral_lname[]" required />
                    <label for="tattoo_width">Last Name</label>
                  </div>
                  <label class="error-message referral_lname_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span><span></span></label>
                </div>
              </div>
              <div class="Orders__referral__input-group">
                <div class="mx-2">
                  <div class="form-floating">
                    <input type="number" class="form-control" style="min-width: 90px;" value="<?php echo $age; ?>" name="referral_age[]" min="18" max="90" required />
                    <label for="tattoo_width">Age</label>
                  </div>
                  <label class="error-message referral_age_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span><span></span></label>
                </div>
                <div class="flex-fill mx-2">
                  <div class="form-floating">
                    <input type="text" class="form-control" value="<?php echo $contact_no; ?>" minlength="7" maxlength="11" placeholder="Contact Number" name="referral_contact_no[]">
                    <label for="tattoo_width">Contact Number</label>
                  </div>
                  <label class="error-message referral_contact_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span><span></span></label>
                </div>
                <div class="flex-fill mx-2">
                  <div class="form-floating">
                    <input type="email" class="form-control" value="<?php echo $email; ?>" maxlength="62" placeholder="Email" name="referral_email[]" required />
                    <label for="tattoo_width">Email</label>
                  </div>
                  <label class="error-message referral_email_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span><span></span></label>
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
        <div class="Order__order__details">
          <div class="row">
            <div class="col">
              <!-- order id -->
              <label class="form-label fw-semibold">Order ID</label>
              <p class="w-auto"><?php echo $order_id; ?></p>
            </div>
            <?php
              $timestamp = explode(' ', $api->sanitize_data($order_date, 'string'));
              $date = date("M:d:Y", strtotime($timestamp[0]));
              $time = date("g:i A", strtotime($timestamp[1]));
              $date = explode(':', $date);
            ?>
            <div class="col">
              <!-- order date -->
              <label class="form-label fw-semibold">Placed on</label>
              <p class="w-auto"><?php echo $api->sanitize_data($date[0], 'string') . " " . $api->sanitize_data($date[1], 'int') . ", " . $api->sanitize_data($date[2], 'int') . ", " . $api->sanitize_data($time, 'string') ?></p>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <!-- incentive -->
              <label class="form-label fw-semibold">Incentive</label>
              <p><?php echo $incentive; ?></p>
            </div>
            <div class="col">
              <!-- amount due total -->
              <label for="status" class="form-label fw-semibold">Amount Due Total</label>
              <p>₱<?php echo $total; ?></p>
            </div>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<?php if(!empty($order_id)){ ?>
  <script>
    // page tabs
    var orders_tab = document.getElementById('Orders__controls--tab--orders');
    var orders = document.getElementById('Orders__orders');
    var order_btn_group = document.getElementById('Orders__controls--orders');

    var referrals_tab = document.getElementById('Orders__controls--tab--referrals');
    var referrals = document.getElementById('Orders__referrals');
    var referral_btn_group = document.getElementById('Orders__controls--referrals');

    // page form
    var page_form = document.getElementById('Orders__form');

    // switching between tabs
    referrals_tab.addEventListener('click', function(){
      orders_tab.className = "Orders__controls--tab border-0 text-muted";
      this.className = "Orders__controls--tab border-0 border-bottom text-black";

      orders.className = "d-none";
      referrals.className = "d-block";

      order_btn_group.classList.replace("d-block", "d-none");
      referral_btn_group.classList.replace("d-none", "d-block");
    });

    orders_tab.addEventListener('click', function(){
      referrals_tab.className = "Orders__controls--tab border-0 text-muted";
      this.className = "Orders__controls--tab border-0 border-bottom text-black";

      orders.className = "d-block";
      referrals.className = "d-none";

      order_btn_group.classList.replace("d-none", "d-block");
      referral_btn_group.classList.replace("d-block", "d-none");
    });
  </script>
  <?php if($editable_rows > 0){ ?>
    <script src="../api/api.js"></script>
    <script src="../scripts/js/orders.js"></script>
  <?php } if($api->num_rows($referrals) > 0){ ?>
    <script src="../scripts/js/referrals.js"></script>
  <?php } ?>
<?php } ?>
</html>
<?php
  if(isset($_SESSION['res'])){
    unset($_SESSION['res']);
  }
  if(isset($_SESSION['first_name_err'])){
    unset($_SESSION['first_name_err']);
  }
  if(isset($_SESSION['mi_err'])){
    unset($_SESSION['mi_err']);
  }
  if(isset($_SESSION['last_name_err'])){
    unset($_SESSION['last_name_err']);
  }
  if(isset($_SESSION['age_err'])){
    unset($_SESSION['age_err']);
  }
  if(isset($_SESSION['email_err'])){
    unset($_SESSION['email_err']);
  }
  if(isset($_SESSION['contact_number_err'])){
    unset($_SESSION['contact_number_err']);
  }
  if(isset($_SESSION['referral_err'])){
    unset($_SESSION['referral_err']);
  }
?>