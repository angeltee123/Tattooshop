<?php
  session_name("sess_id");
  session_start();
  // if(!isset($_SESSION['user']['user_id']) || strcasecmp($_SESSION['user']['user_type'], "Admin") != 0){
  //   Header("Location: ../client/index.php");
  //   die();
  // } else {
    require_once '../api/api.php';
    $api = new api();
  // }

  try {
    $statement = $api->prepare("SELECT workorder.client_id, client_fname, client_lname, user_avatar, order_id, order_date, amount_due_total, incentive FROM ((client INNER JOIN user ON client.client_id=user.client_id) INNER JOIN workorder ON client.client_id=workorder.client_id) WHERE status=? ORDER BY order_date ASC");
    if($statement===false){
      throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
    }

    $mysqli_checks = $api->bind_params($statement, "s", "Ongoing");
    if($mysqli_checks===false){
      throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
    }

    $mysqli_checks = $api->execute($statement);
    if($mysqli_checks===false){
      throw new Exception('Execute error: The prepared statement could not be executed.');
    }

    $orders = $api->get_result($statement);
    if($orders===false){
      throw new Exception('get_result() error: Getting result set from statement failed.');
    }

    $api->free_result($statement);
    $mysqli_checks = $api->close($statement);
    ($mysqli_checks===false) ? throw new Exception('The prepared statement could not be closed.') : $statement = null;
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
  <!-- native style -->
  <link href="../style/orders.css" rel="stylesheet">
  <style>
    .Order__order__details {
      margin: 0 !important;
    }

    .Orders__order__collapsible {
      display: flex;
      flex-flow: row nowrap;
      justify-content: space-between;
      align-items: center;
      border: 1px solid #dee2e6 !important;
      border-radius: 50rem !important;
    }

    .Orders__order__collapsible--toggler {
      display: flex;
      flex-flow: row wrap;
      justify-content: flex-start;
      align-items: center;
      text-align: left;
      background-color: rgba(255, 255, 255, 0) !important;
      padding: 3.25rem !important;
      border: 0 !important;
    }

    .Orders__order__collapsible__avatar {
      height: 50px !important;
      width: 50px !important;
      border: 1px solid #dee2e6 !important;
    }

    .Orders__order__collapsible__header {
      display: block;
      margin: 0 0.35rem 0 1.25rem !important;
    }

    .Orders__order__collapsible__date {
      font-size: 1rem;
      color: #6c757d;
      margin: 0;
    }

    .Orders__order {
      transition: margin .5s;
    }

    .Orders__order-item {
      border-top: 1px solid #dee2e6 !important;
    }

    .Orders__referral {
      border: 0 !important;
    }

    @media (max-width: 914px){
      .Orders__order__collapsible__header {
        display: none !important;
      }

      .Orders__order__collapsible__date {
        font-size: 1.25rem !important;
        color: #000000 !important;
        margin: 0 0 0 0.5rem !important;
      }
    }
  </style>
  <title>Orders | NJC Tattoo</title>
</head>
<body class="w-100">
  <?php require_once '../common/header.php'; ?>
  <div class="Orders content">
    <div class="Orders__header">
      <h2 class="fw-bold display-3">Orders</h2>
      <p class="d-inline fs-5 text-muted">Manage all your ongoing orders and client referrals here. <?php if(isset($workorder)){ echo "Tick the checkboxes of the items you want to modify or remove."; } ?></p>
    </div>
    <div class="Orders__controls">
      <div>
        <button type="button" class="Orders__controls--tab border-0 border-bottom text-black" id="Orders__controls--tab--all-items">All items</button>
        <button type="button" class="Orders__controls--tab border-0 text-muted" id="Orders__controls--tab--orders">Orders only</button>
        <button type="button" class="Orders__controls--tab border-0 text-muted" id="Orders__controls--tab--referrals">Referrals only</button>
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
            $user_avatar = $api->sanitize_data($order['user_avatar'], "string");
            $order_id = $api->sanitize_data($order['order_id'], "string");
            $total = number_format($api->sanitize_data($order['amount_due_total'], "float"), 2, '.', '');
            $incentive = $api->sanitize_data($order['incentive'], "string");
      ?>
      <div class="Orders__order my-2">
        <div class="Orders__order__collapsible collapsible">
          <button type="button" class="Orders__order__collapsible--toggler col" data-bs-toggle="collapse" data-bs-target="#order_<?php echo $order_id; ?>" aria-expanded="false" aria-controls="order_<?php echo $order_id; ?>">
            <div class="Orders__order__collapsible__avatar avatar" style="background-image: url(<?php echo $user_avatar; ?>)"></div>
            <h5 class="Orders__order__collapsible__header">Order placed by
              <?php
                $datetime = explode('-', $api->sanitize_data($order['order_date'], "string"));
                $timestamp = explode(' ', $api->sanitize_data($order['order_date'], "string"));
                $date = date("M:d:Y", strtotime($timestamp[0]));
                $time = date("g:i A", strtotime($timestamp[1]));
                $date = explode(':', $date);
                echo $client_fname . " " . $client_lname;
              ?>
            </h5>
            <p class="Orders__order__collapsible__date"><?php echo " on " . $api->sanitize_data($date[0], "string") . " " . $api->sanitize_data($date[1], "int") . ", " . $api->sanitize_data($date[2], "int"); ?></p>
          </button>
          <form method="POST" action="./checkout.php" class="w-auto mx-3 pe-5">
            <input type="hidden" readonly class="d-none" value="<?php echo $client_id; ?>" name="client_id" />
            <input type="hidden" readonly class="d-none" value="<?php echo $order_id; ?>" name="order_id" />
            <button type="submit" class="Orders__controls__control btn btn-outline-dark me-1" name="order_checkout" data-bs-toggle="tooltip" data-bs-placement="top" title="Log Payment"><span class="Orders__controls__control__icon material-icons">shopping_cart_checkout</span><span class="Orders__controls__control__text">Log Payment</span></button>
          </form>
        </div>
        <div class="collapse mt-3 Orders__order__collapsible__body" id="order_<?php echo $order_id; ?>">
          <div class="Order__order__details">
            <div class="row">
              <div class="col">
                <!-- order id -->
                <label class="form-label fw-semibold">Order ID</label>
                <p class="w-auto"><?php echo $order_id; ?></p>
              </div>
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
          <div class="Orders__order__items d-block">
            <?php
              try {    
                $retrieve_items = $api->prepare("SELECT order_item.item_id, tattoo_name, tattoo_image, tattoo_price, tattoo_quantity, order_item.tattoo_width, order_item.tattoo_height, paid, item_status, amount_addon FROM ((order_item INNER JOIN tattoo ON order_item.tattoo_id=tattoo.tattoo_id) LEFT JOIN reservation ON order_item.item_id=reservation.item_id) WHERE order_id=? ORDER BY paid ASC, item_status DESC");
                if($retrieve_items===false){
                  throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }
      
                $mysqli_checks = $api->bind_params($retrieve_items, "s", $order_id);
                if($mysqli_checks===false){
                  throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                }
      
                $mysqli_checks = $api->execute($retrieve_items);
                if($mysqli_checks===false){
                  throw new Exception('Execute error: The prepared statement could not be executed.');
                }
      
                $items = $api->get_result($retrieve_items);
                if($items===false){
                  throw new Exception('get_result() error: Getting result set from statement failed.');
                }

                $api->free_result($retrieve_items);
                $mysqli_checks = $api->close($retrieve_items);
                if($mysqli_checks===false){
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
            <<?php if((strcasecmp($item_status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)){ ?>form action="./queries.php" method="POST"<?php } else { ?>div<?php } ?> class="Orders__order-item">
              <div class="Orders__order-item__preview">
                <div class="Orders__order-item__tattoo-preview shadow-sm" style="background-image: url(<?php echo $tattoo_image; ?>)"></div>
              </div>
              <div class="Orders__order-item__details">
                <div class="row my-5">
                  <div class="col">
                    <!-- tattoo name -->
                    <label class="form-label fw-semibold">Item</label>
                    <p><?php echo $tattoo_name; ?></p>
                    <input type="hidden" readonly class="d-none" value="<?php echo $item_id; ?>" name="item_id" />
                  </div>
                  <div class="col">
                    <!-- item status -->
                    <label class="form-label fw-semibold">Item Status</label>
                    <p><?php echo $item_status; ?></p>
                    <?php if((strcasecmp($item_status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)){ ?>
                      <input type="hidden" class="d-none" name="status[]" value="<?php echo $item_status; ?>" />
                    <?php } ?>
                  </div>
                  <div class="col">
                    <!-- amount_addon -->
                    <label class="form-label fw-semibold">Amount Addon</label>
                    <p><?php echo ($addon == 0) ? "N/A" : "₱" . $addon; ?></p>
                  </div>
                  <div class="col">
                    <!-- payment status -->
                    <label class="form-label fw-semibold">Payment Status</label>
                    <p><?php echo $paid; ?></p>
                    <?php if((strcasecmp($item_status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)){ ?>
                      <input type="hidden" class="d-none" name="paid[]" value="<?php echo $paid; ?>" />
                    <?php } ?>
                  </div>
                </div>
                <div class="row my-5">
                  <div class="col">
                    <!-- price -->
                    <label class="form-label fw-semibold">Price</label>
                    <p>₱<?php echo $price; ?></p>
                  </div>
                  <div class="col">
                    <!-- quantity -->
                    <label for="quantity" class="form-label fw-semibold">Quantity</label>
                    <?php if((strcasecmp($item_status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)){ ?>
                      <input type="number" class="form-control" name="quantity" min="1" value="<?php echo $quantity; ?>"/>
                      <label class="error-message quantity_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span></label>
                    <?php } else { ?>
                      <p><?php echo $quantity; ?></p>
                    <?php } ?>
                  </div>
                  <div class="col">
                    <!-- width -->
                    <label for="width" class="form-label fw-semibold">Width</label>
                    <?php if((strcasecmp($item_status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)){ ?>
                      <input type="number" class="form-control" name="width" min="1" value="<?php echo $width; ?>"/>
                      <label class="error-message width_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span></label>
                    <?php } else { ?>
                      <p><?php echo $width; ?></p>
                    <?php } ?>
                  </div>
                  <div class="col">
                    <!-- height --->
                    <label for="height" class="form-label fw-semibold">Height</label>
                    <?php if((strcasecmp($item_status, "Standing") == 0 && strcasecmp($paid, "Unpaid") == 0)){ ?>
                      <input type="number" class="form-control" name="height" min="1" value="<?php echo $height; ?>"/>
                      <label class="error-message height_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span></label>
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
          <div class="Orders__order__referrals d-block">
          <?php
            try {    
              $retrieve_referrals = $api->prepare("SELECT referral_id, referral_fname, referral_mi, referral_lname, referral_contact_no, referral_email, referral_age, confirmation_status FROM (workorder INNER JOIN referral ON workorder.order_id=referral.order_id) WHERE referral.order_id=? AND referral.client_id=?");
              if($retrieve_referrals===false){
                  throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
              }
    
              $mysqli_checks = $api->bind_params($retrieve_referrals, "ss", array($order_id, $client_id));
              if($mysqli_checks===false){
                  throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
              }
    
              $mysqli_checks = $api->execute($retrieve_referrals);
              if($mysqli_checks===false){
                  throw new Exception('Execute error: The prepared statement could not be executed.');
              }
    
              $referrals = $api->get_result($retrieve_referrals);
              if($referrals===false){
                throw new Exception('get_result() error: Getting result set from statement failed.');
              }

              $api->free_result($retrieve_referrals);
              $mysqli_checks = $api->close($retrieve_referrals);
              if($mysqli_checks===false){
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
            <form method="POST" action="./queries.php" class="d-flex justify-content-start align-items-center border-top">
              <div class="Orders__referral">
                <div class="Orders__referral__input-group">
                  <div class="<?php echo (in_array($confirmation_status, array("Confirmed", "Declined"))) ? "col" : "flex-fill mx-2"; ?>">
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
                        <label class="error-message referral_status_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span></label>                
                      </div>
                    <?php } ?>
                  </div>
                  <div class="<?php echo (in_array($confirmation_status, array("Confirmed", "Declined"))) ? "col" : "flex-fill mx-2"; ?>">
                    <?php if(in_array($confirmation_status, array("Confirmed", "Declined"))){ ?>
                    <label class="fw-semibold">First Name</label>   
                    <p class="my-0"><?php echo $first_name; ?></p>
                    <?php } else { ?>
                      <div class="form-floating">
                        <input type="text" class="form-control" value="<?php echo $first_name; ?>" maxlength="50" placeholder="First Name" name="referral_fname" required />
                        <label for="referral_fname">First Name</label>
                        <label class="error-message referral_fname_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span></label>
                      </div>
                    <?php } ?> 
                  </div>
                  <div class="<?php echo (in_array($confirmation_status, array("Confirmed", "Declined"))) ? "col" : "mx-2"; ?>">
                    <?php if(in_array($confirmation_status, array("Confirmed", "Declined"))){ ?>
                    <label class="fw-semibold">Middle Initial</label>   
                    <p class="my-0"><?php echo $mi; ?>.</p>
                    <?php } else { ?>
                      <div class="form-floating">
                        <input type="text" class="form-control" style="width: 50px;" value="<?php echo $mi; ?>" minlength="1" maxlength="1" placeholder="MI" name="referral_mi" required />
                        <label for="referral_mi">M.I.</label>
                        <label class="error-message referral_mi_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span></label>
                      </div>
                    <?php } ?>
                  </div>
                  <div class="<?php echo (in_array($confirmation_status, array("Confirmed", "Declined"))) ? "col" : "flex-fill mx-2"; ?>">
                    <?php if(in_array($confirmation_status, array("Confirmed", "Declined"))){ ?>
                    <label class="fw-semibold">Last Name</label>   
                    <p class="my-0"><?php echo $last_name; ?></p>
                    <?php } else { ?>
                      <div class="form-floating">
                        <input type="text" class="form-control" value="<?php echo $last_name; ?>" maxlength="50" placeholder="Last Name" name="referral_lname" required />
                        <label for="referral_lname">Last Name</label>
                        <label class="error-message referral_lname_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span></label>
                      </div>
                    <?php } ?>
                  </div>
                </div>
                <div class="Orders__referral__input-group">
                  <div class="<?php echo (in_array($confirmation_status, array("Confirmed", "Declined"))) ? "col-2" : "mx-2"; ?>">
                    <?php if(in_array($confirmation_status, array("Confirmed", "Declined"))){ ?>
                    <label class="fw-semibold">Age</label>   
                    <p class="my-0"><?php echo $age; ?></p>
                    <?php } else { ?>
                      <div class="form-floating">
                        <input type="number" class="form-control" style="width: 60px;" value="<?php echo $age; ?>" name="referral_age" min="17" max="90" required />
                        <label for="referral_age">Age</label>
                        <label class="error-message referral_age_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span></label>
                      </div>
                    <?php } ?>
                  </div>
                  <div class="<?php echo (in_array($confirmation_status, array("Confirmed", "Declined"))) ? "col" : "flex-fill mx-2"; ?>">
                    <?php if(in_array($confirmation_status, array("Confirmed", "Declined"))){ ?>
                    <label class="fw-semibold">Contact Number</label>   
                    <p class="my-0"><?php echo $contact_no; ?></p>
                    <?php } else { ?>
                      <div class="form-floating">
                        <input type="text" class="form-control" value="<?php echo $contact_no; ?>" minlength="7" maxlength="11" placeholder="Contact Number" name="referral_contact_no">
                        <label for="referral_contact_no">Contact Number</label>
                        <label class="error-message referral_contact_number_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span></label>
                      </div>
                    <?php } ?>
                  </div>
                  <div class="<?php echo (in_array($confirmation_status, array("Confirmed", "Declined"))) ? "col" : "flex-fill mx-2"; ?>">
                    <?php if(in_array($confirmation_status, array("Confirmed", "Declined"))){ ?>
                    <label class="fw-semibold">Email</label>   
                    <p class="my-0"><?php echo $email; ?></p>
                    <?php } else { ?>
                      <div class="form-floating">
                        <input type="email" class="form-control" value="<?php echo $email; ?>" maxlength="62" placeholder="Email" name="referral_email" required />
                        <label for="referral_email">Email</label>
                        <label class="error-message referral_email_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span></label>
                      </div>
                    <?php } ?>
                  </div>
                </div>
              </div>
              <div class="w-auto">
                <div class="Orders__referral__input-group">
                  <?php if(strcasecmp($confirmation_status, "Pending") == 0){ ?>
                    <div class="d-inline-block">
                      <button type="submit" class="Orders__controls__control btn btn-outline-primary me-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Update Referral" name="update_referral"><span class="Orders__controls__control__icon material-icons">edit</span</button>
                    </div>
                  <?php } ?>
                  <div class="d-inline-block">
                    <button type="submit" class="Orders__controls__control btn btn-outline-danger me-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove Referral" name="remove_referral"><span class="Orders__controls__control__icon material-icons">person_remove</span></button>
                  </div>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script>
  // tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl){
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // collapsibles
  var show_orders = false;
  var toggle_orders = document.getElementById('toggle_orders');
  var orders = document.getElementsByClassName('Orders__order');
  var order_collapsibles = document.getElementsByClassName('Orders__order__collapsible__body');

  // tab controls
  var all_items_tab = document.getElementById('Orders__controls--tab--all-items');
  var orders_tab = document.getElementById('Orders__controls--tab--orders');
  var referrals_tab = document.getElementById('Orders__controls--tab--referrals');

  // tab sections
  var items = document.getElementsByClassName('Orders__order__items');
  var referrals = document.getElementsByClassName('Orders__order__referrals');
  
  // collapsibles responsive stacking
  for(var i=0, count=orders.length; i < count; i++){
    let order = orders[i];

    order_collapsibles[i].addEventListener('shown.bs.collapse', function (){
      order.classList.replace('my-2', 'my-4');
    });

    order_collapsibles[i].addEventListener('hidden.bs.collapse', function (){
      order.classList.replace('my-4', 'my-2');
    });
  }

  // toggling all collapsibles
  toggle_orders.addEventListener('click', function(){
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

  // switching between tabs
  all_items_tab.addEventListener('click', function(){
    this.className = "Orders__controls--tab border-0 border-bottom text-black";
    orders_tab.className = "Orders__controls--tab border-0 text-muted";
    referrals_tab.className = "Orders__controls--tab border-0 text-muted";

    for(var i=0, count=items.length; i < count; i++){
      items[i].classList.replace('d-none', 'd-block');
    }

    for(var i=0, count=referrals.length; i < count; i++){
      referrals[i].classList.remove('d-none', 'd-block');
    }
  });

  orders_tab.addEventListener('click', function(){
    this.className = "Orders__controls--tab border-0 border-bottom text-black";
    referrals_tab.className = "Orders__controls--tab border-0 text-muted";
    all_items_tab.className = "Orders__controls--tab border-0 text-muted";

    for(var i=0, count=items.length; i < count; i++){
      items[i].classList.replace('d-none', 'd-block');
    }

    for(var i=0, count=referrals.length; i < count; i++){
      referrals[i].classList.replace('d-block', 'd-none');
    }
  });

  referrals_tab.addEventListener('click', function(){
    this.className = "Orders__controls--tab border-0 border-bottom text-black";
    orders_tab.className = "Orders__controls--tab border-0 text-muted";
    all_items_tab.className = "Orders__controls--tab border-0 text-muted";

    for(var i=0, count=items.length; i < count; i++){
      items[i].classList.replace('d-block', 'd-none');
    }

    for(var i=0, count=referrals.length; i < count; i++){
      referrals[i].classList.remove('d-none', 'd-block');
    }
  });
</script>
</html>
<?php
  if(isset($_SESSION['res'])){
    unset($_SESSION['res']);
  }
?>