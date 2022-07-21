<?php
  session_name("sess_id");
  session_start();

  // navigation guard
  if(!isset($_SESSION['user']['user_id']) || strcasecmp($_SESSION['user']['user_type'], "User") != 0){
    Header("Location: ../admin/index.php");
    die();
  } else {
    require_once '../api/api.php';
    $api = new api();
  }
  
  // retreive ongoing reservations
  try {
    $client_id = $api->sanitize_data($_SESSION['user']['client_id'], 'string');
    $mysqli_checks = $api->get_workorder($client_id);
    ($mysqli_checks!==true) ? throw new Exception('Error: Retrieving client workorder failed.') : $order_id = $api->sanitize_data($_SESSION['order']['order_id'], 'string');
    
    $statement = $api->prepare("SELECT reservation.reservation_id, reservation_status, reservation.item_id, tattoo_name, tattoo_image, tattoo_quantity, paid, order_item.tattoo_width, order_item.tattoo_height, service_type, scheduled_date, scheduled_time, reservation_address, reservation_description, amount_addon FROM (((reservation INNER JOIN order_item ON reservation.item_id=order_item.item_id) INNER JOIN tattoo ON tattoo.tattoo_id=order_item.tattoo_id) LEFT JOIN worksession ON worksession.reservation_id=reservation.reservation_id) WHERE order_id=? AND item_status=? AND reservation_status IN (?, ?) AND session_id IS NULL ORDER BY scheduled_date ASC, scheduled_time ASC, reservation_status ASC");
    if($statement===false){
      throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
    }

    $mysqli_checks = $api->bind_params($statement, "ssss", array($order_id, "Reserved", "Pending", "Confirmed"));
    if($mysqli_checks===false){
      throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
    }

    $mysqli_checks = $api->execute($statement);
    if($mysqli_checks===false){
      throw new Exception('Execute error: The prepared statement could not be executed.');
    }

    $reservations = $api->get_result($statement);
    if($reservations===false){
      throw new Exception('get_result() error: Getting result set from statement failed.');
    }

    $api->free_result($statement);
    $mysqli_checks = $api->close($statement);
    ($mysqli_checks===false) ? throw new Exception('The prepared statement could not be closed.') : $statement = null;

    // retrieve all standing order items
    $statement = $api->prepare("SELECT item_id, paid, tattoo_name, tattoo_quantity FROM ((tattoo JOIN order_item ON tattoo.tattoo_id=order_item.tattoo_id) JOIN workorder ON order_item.order_id=workorder.order_id) WHERE client_id=? AND item_status=? AND tattoo_quantity !=?");
    if($statement===false){
      throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
    }

    $mysqli_checks = $api->bind_params($statement, "ssi", array($client_id, "Standing", 0));
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

    $api->free_result($statement);
    $mysqli_checks = $api->close($statement);
    if($mysqli_checks===false){
      throw new Exception('The prepared statement could not be closed.');
    }
  } catch (Exception $e) {
    $_SESSION['res'] = $e->getMessage();
    Header("Location: ../client/index.php");
    exit();
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <!-- meta -->
  <?php require_once '../common/meta.php'; ?>
  
  <!-- external stylesheets -->
  <link href="../style/reservations.css" rel="stylesheet" scoped>
  <title>Bookings | NJC Tattoo</title>
</head>
<body>
  <!-- navigation bar -->
  <?php require_once '../common/header.php'; ?>
  
  <!-- page content -->
  <div class="Reservations content">
    <!-- page header -->
    <div class="page-header">
      <h2 class="fw-bold display-3">Reservations</h2>
      <p class="d-inline fs-5 text-muted">Make reservations for your tattoo orders and manage your ongoing reservations here.</p>
    </div>

    <!-- page controls -->
    <div class="controls">
      <?php if($api->num_rows($reservations) > 0){ ?>
        <!-- toggle all collapsibles -->
        <button type="button" class="btn btn-link btn-lg text-black text-decoration-none me-1" id="toggle_reservations">Show All Reservations</button>
      <?php } ?>

      <div class="d-flex justify-content-between align-items-center">
        <?php if($api->num_rows($reservations) > 0){ ?>
          <!-- toggle editing -->
          <div class="me-3 form-check form-switch">
            <input class="form-check-input" type="checkbox" id="edit_reservations" />
            <label class="form-check-label" for="edit_reservations" id="edit_reservations_label">Edit</label>
          </div>
        <?php } if(!empty($order_id)){ ?>
          <!-- new reservation collapsible toggler -->
          <button type="submit" class="control btn btn-outline-dark me-2" data-bs-toggle="collapse" data-bs-target="#new_reservation" aria-expanded="false" aria-controls="new_reservation" title="New Reservation"><span class="control__icon material-icons">bookmark_add</span><span class="control__text">New Reservation</span></button>
        <?php } ?>
      </div>
    </div>

    <!-- new reservation collapsible body -->
    <div class="collapse border-bottom rounded mt-3 mb-7 py-7" id="new_reservation">
      <form action="./new_reservation.php" method="post">
        <label for="item" class="form-label text-muted">Reservation Item</label>
        <div class="input-group">
          <!-- select reservation item -->
          <select class="form-select form-select-lg" name="item">
            <?php
              // extract sql row data
              if($api->num_rows($res) > 0){
                while($item = $api->fetch_assoc($res)){
                  $item_id = $api->sanitize_data($item['item_id'], "string"); 
                  $paid = $api->sanitize_data($item['paid'], "string"); 
                  $name = $api->sanitize_data($item['tattoo_name'], "string"); 
                  $quantity = $api->sanitize_data($item['tattoo_quantity'], "int"); 
            ?>
              <option value="<?php echo $item_id; ?>"><?php echo $quantity . " pc. ". $name; if(strcasecmp($paid, "Unpaid") == 0){ echo " (Unpaid)"; } else { echo " (Paid)"; } ?></option>
            <?php }} else { ?>
              <option value="" selected>No items available for resevation.</option>
            <?php } ?>
          </select>
          
          <?php if($api->num_rows($res) > 0){ ?>
            <!-- book new reservation -->
            <button type="submit" class="btn btn-outline-dark d-flex align-items-center"><span class="material-icons md-48 lh-base">add</span>New Reservation</button>
          <?php } ?>
        </div>
      </form>
    </div>
    <?php if($api->num_rows($reservations) > 0){ ?>
      <!-- list of ongoing reservations -->
      <div class="reservations vstack">
        <?php
          // extract sql row data
          if($api->num_rows($reservations) > 0){
            while($reservation = $api->fetch_assoc($reservations)){
              $reservation_id = $api->sanitize_data($reservation['reservation_id'], "string");
              $status = $api->sanitize_data($reservation['reservation_status'], "string");
              $item_id = $api->sanitize_data($reservation['item_id'], "string");
              $tattoo_name = $api->sanitize_data($reservation['tattoo_name'], "string");
              $tattoo_image = $api->sanitize_data($reservation['tattoo_image'], "string");
              $quantity = $api->sanitize_data($reservation['tattoo_quantity'], "int");
              $paid = $api->sanitize_data($reservation['paid'], "string");
              $width = $api->sanitize_data($reservation['tattoo_width'], "int");
              $height = $api->sanitize_data($reservation['tattoo_height'], "int");
              $service_type = $api->sanitize_data($reservation['service_type'], "string");
              $scheduled_date = $api->sanitize_data($reservation['scheduled_date'], "string");
              $scheduled_time = $api->sanitize_data($reservation['scheduled_time'], "string");
              $address = $api->sanitize_data($reservation['reservation_address'], "string");
              $description = $api->sanitize_data($reservation['reservation_description'], "string");
              $addon = number_format($api->sanitize_data($reservation['amount_addon'], "float"), 2, '.', '');
        ?>
          <!-- ongoing reservation -->
          <div class="Reservations__item my-2">

            <!-- collapsible -->
            <div class="Reservations__item__collapsible collapsible">

              <!-- collapsible toggler -->
              <button type="button" class="Reservations__item__collapsible--toggler col" data-bs-toggle="collapse" data-bs-target="#item_<?php echo $item_id; ?>" aria-expanded="false" aria-controls="item_<?php echo $item_id; ?>">
                <div class="Reservations__item__collapsible__avatar avatar" style="background-image: url(<?php echo $tattoo_image; ?>)"></div>
                <h5 class="Reservations__item__collapsible__header">
                  <?php
                    $date = date("M:d:Y", strtotime($scheduled_date));
                    $date = explode(':', $date);
                    echo $quantity . " pc. " . $tattoo_name;
                  ?>
                </h5>
                <p class="Reservations__item__collapsible__date"><?php echo " on " . $api->sanitize_data($date[0], "string") . " " . $api->sanitize_data($date[1], "int") . ", " . $api->sanitize_data($date[2], "int"); ?></p>
              </button>
            </div>

            <!-- collapsible body -->
            <div class="Reservations__item__collapsible__body collapse mt-3 p-7 rounded shadow-sm" id="item_<?php echo $item_id; ?>">
              
              <!-- collapsible form -->
              <form class="Reservations__item__form" action="../scripts/php/queries.php" method="POST">
                <div class="Reservations__item__collapsible__body--stacking">

                  <!-- item preview -->
                  <div class="Reservations__item__collapsible__body__preview order-first">
                    <div class="Reservations__item__collapsible__body__tattoo-preview" style="background-image: url(<?php echo $tattoo_image; ?>)"></div>
                  </div>

                  <!-- details -->
                  <div class="Reservations__item__collapsible__body--stacking__item-details order-last">
                    <div class="row">
                      <!-- item name -->
                      <div class="col-6 col-md my-2">
                        <label class="form-label fw-semibold">Reserved Item</label>
                        <p><?php echo $tattoo_name; ?></p>
                        <input type="hidden" readonly class="d-none" value="<?php echo $item_id; ?>" name="item_id" />
                      </div>

                      <!-- itemstatus -->
                      <div class="col-6 col-md my-2">
                        <label for="status" class="form-label fw-semibold">Status</label>
                        <div class="fw-semibold"><p class="Reservation__item__status d-inline <?php echo (strcasecmp($status, "Confirmed") == 0) ? "text-success" : "text-secondary"; ?>"><?php echo $status; ?></p>, <p class="d-inline <?php echo (strcasecmp($paid, "Fully Paid") == 0) ? "text-success" : "text-secondary"; ?>"><?php echo $paid; ?></p></div>
                        <input type="hidden" readonly class="d-none" value="<?php echo $reservation_id; ?>" name="reservation_id" />
                      </div>
                      
                      <!-- reservation addon amount -->
                      <div class="col-12 col-md my-2">
                        <label class="form-label fw-semibold">Add-on Amount</label>
                        <p class="mb-0">₱<?php echo $addon; ?></p>
                      </div>
                    </div>

                    <div class="row">
                      <!-- item quantity -->
                      <div class="col col-md my-2">
                        <label for="quantity" class="form-label fw-semibold">Quantity</label>
                        <p><?php echo $quantity . " pc. "; ?></p>
                        <input type="hidden" readonly class="d-none" value="<?php echo $quantity; ?>" name="quantity" />
                      </div>

                      <!-- tattoo width -->
                      <div class="col col-md my-2">
                        <label for="width" class="form-label fw-semibold">Width</label>
                        <p><?php echo $width . " in."; ?></p>
                      </div>
                      <!-- tattoo height --->
                      <div class="col col-md my-2">
                        <label for="height" class="form-label fw-semibold">Height</label>
                        <p><?php echo $height . " in."; ?></p>
                      </div>
                    </div>

                    <div class="row">
                      <!-- service type -->
                      <div class="col-12 col-md my-2">
                        <label for="service_type" class="form-label fw-semibold">Service Type</label>
                        <?php if(strcasecmp($status, "Confirmed") == 0){ ?>
                          <p><?php echo $service_type; ?></p>
                          <input type="hidden" readonly class="d-none" value="<?php echo $service_type; ?>" name="service_type" />
                        <?php } else { ?>
                          <select name="service_type" class="reservations form-select form-select-plaintext no-select mb-3">
                            <option value="Walk-in" <?php echo strcasecmp($service_type, 'Walk-in') == 0 ? "selected" : "disabled"; ?>>Walk-in</option>
                            <option value="Home Service" <?php echo strcasecmp($service_type, 'Home service') == 0 ? "selected" : "disabled"; ?>>Home Service</option>
                          </select>
                        <?php } ?>
                      </div>

                      <!-- time -->
                      <div class="col-12 col-md my-2">
                        <label for="scheduled_time" class="form-label fw-semibold">Scheduled Time</label>
                        <?php if(strcasecmp($status, "Confirmed") == 0){ ?>
                          <p><?php echo $api->sanitize_data(date("g:i A", strtotime($scheduled_time)), 'string'); ?></p>
                        <?php } ?>
                        <input type="<?php echo strcasecmp($status, "Confirmed") == 0 ? "hidden" : "time"; ?>" readonly class="<?php if(strcasecmp($status, "Pending") == 0){ echo "reservations "; } ?>form-control" value="<?php echo $scheduled_time; ?>" name="scheduled_time" />
                        <label class="error-message scheduled_time_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span><span></span></label>
                      </div>

                      <!-- date -->
                      <div class="col-12 col-md my-2">
                        <label for="scheduled_date" class="form-label fw-semibold">Scheduled Date</label>
                        <?php if(strcasecmp($status, "Confirmed") == 0){ ?>
                          <p><?php echo $api->sanitize_data($date[0], 'string') . " " . $api->sanitize_data($date[1], 'int') . ", " . $api->sanitize_data($date[2], 'int'); ?></p>
                        <?php } ?>
                        <input type="<?php echo strcasecmp($status, "Confirmed") == 0 ? "hidden" : "date"; ?>" readonly class="<?php if(strcasecmp($status, "Pending") == 0){ echo "reservations "; } ?>form-control" value="<?php echo $scheduled_date; ?>" name="scheduled_date" />
                        <label class="error-message scheduled_date_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span><span></span></label>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="Reservations__item__collapsible__body__item-details">
                  <!-- address -->
                  <div>
                    <label for="reservation_address" class="form-label fw-semibold">Address</label>
                    <?php if(strcasecmp($status, "Confirmed") == 0){ ?>
                      <p><?php echo $address; ?></p>
                    <?php } ?>
                    <input type="<?php echo strcasecmp($status, "Confirmed") == 0 ? "hidden" : "text"; ?>" readonly class="<?php echo strcasecmp($status, "Pending") == 0 ? "reservations form-control" : "d-none"; ?>" value="<?php echo $address; ?>" name="reservation_address" />
                    <label class="error-message reservation_address_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span><span></span></label>
                  </div>

                  <!-- demands -->
                  <div>
                    <label for="reservation_description" class="form-label fw-semibold">Demands</label>
                    <?php if(strcasecmp($status, "Confirmed") == 0){ ?>
                      <p><?php echo $description; ?></p>
                    <?php } else { ?>
                      <textarea readonly class="<?php if(strcasecmp($status, "Pending") == 0) echo "reservations "; ?>form-control p-3 text-wrap" name="reservation_demands" rows="5" placeholder="Reservation Demands"><?php echo $description; ?></textarea>
                      <p class="my-2 d-none text-danger"></p>
                    <?php } ?>
                  </div>
                </div>

                <?php if(strcasecmp($status, 'Pending') == 0){ ?>
                  <!-- item controls -->
                  <div class="mt-5 mb-2">
                    <div class="d-flex justify-content-end">
                      <!-- update reservation details -->
                      <button type="submit" class="control btn btn-outline-secondary order-first d-none" name="update_reservation" data-bs-toggle="tooltip" data-bs-placement="top" title="Update Reservation"><span class="control__icon material-icons">edit</span><span class="control__text">Update</span></button>

                      <!-- confirm reservation -->
                      <button type="submit" class="control btn btn-outline-primary order-1 ms-1" name="confirm_reservation" data-bs-toggle="tooltip" data-bs-placement="top" title="Confirm Reservation"><span class="control__icon material-icons">turned_in</span><span class="control__text">Confirm Reservation</span></button>
                      
                      <?php if(strcasecmp($paid, 'Fully Paid') != 0){ ?>
                        <!-- cancel reservation -->
                        <button type="submit" class="control btn btn-outline-danger order-last ms-1" name="cancel_reservation" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel Reservation"><span class="control__icon material-icons">bookmark_remove</span><span class="control__text">Cancel</span></button>
                      <?php } ?>
                    </div>
                  </div>
                <?php } ?>
              </form>
            </div>
          </div>
        <?php }} else { ?>
          <h1 class="my-5 display-2 fst-italic text-muted no-select">No ongoing reservations.</h1>
        <?php } ?>
      </div>
    <?php } else { ?>
      <div class="w-100 p-7">
        <h1 class="display-2 fst-italic text-muted no-select">You currently have no ongoing reservations.</h1>
      </div>
    <?php } ?>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<?php if($api->num_rows($reservations) > 0){ ?>
  <script src="../api/api.js"></script>
  <script src="../scripts/js/reservations.js"></script>
<?php } ?>
</html>