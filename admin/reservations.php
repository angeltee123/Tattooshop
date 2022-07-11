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
    $item_count = 0;

    // retrieving ongoing worksessions
    $statement = $api->prepare("SELECT client_fname, client_mi, client_lname, user_avatar, worksession.reservation_id, session_id, session_status, reservation.item_id, tattoo_name, tattoo_image, tattoo_quantity, order_item.tattoo_width, order_item.tattoo_height, service_type, amount_addon, session_date, session_start_time, session_address, reservation_description FROM ((((((workorder INNER JOIN order_item ON workorder.order_id=order_item.order_id) INNER JOIN client ON workorder.client_id=client.client_id) INNER JOIN user ON workorder.client_id=user.client_id) INNER JOIN reservation ON reservation.item_id=order_item.item_id) INNER JOIN worksession ON reservation.reservation_id=worksession.reservation_id) INNER JOIN tattoo ON tattoo.tattoo_id=order_item.tattoo_id) WHERE session_status!= ? ORDER BY session_date ASC");
    if($statement===false){
      throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
    }

    $mysqli_checks = $api->bind_params($statement, "s", "Finished");
    if($mysqli_checks===false){
      throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
    }

    $mysqli_checks = $api->execute($statement);
    if($mysqli_checks===false){
      throw new Exception('Execute error: The prepared statement could not be executed.');
    }

    $worksessions = $api->get_result($statement);
    if($worksessions===false){
      throw new Exception('get_result() error: Getting result set from statement failed.');
    }

    $item_count += $api->num_rows($worksessions);

    $api->free_result($statement);
    $mysqli_checks = $api->close($statement);
    ($mysqli_checks===false) ? throw new Exception('The prepared statement could not be closed.') : $statement = null;

    // retrieving ongoing reservations
    $statement = $api->prepare("SELECT workorder.client_id, client_fname, client_mi, client_lname, user_avatar, reservation_id, reservation_status, reservation.item_id, tattoo_name, tattoo_image, tattoo_quantity, paid, order_item.tattoo_width, order_item.tattoo_height, service_type, amount_addon, scheduled_date, scheduled_time, reservation_address, reservation_description FROM (((((workorder INNER JOIN order_item ON workorder.order_id=order_item.order_id) INNER JOIN client ON workorder.client_id=client.client_id) INNER JOIN user ON workorder.client_id=user.client_id) INNER JOIN reservation ON reservation.item_id=order_item.item_id) INNER JOIN tattoo ON tattoo.tattoo_id=order_item.tattoo_id) WHERE reservation_id NOT IN (SELECT reservation_id FROM worksession) AND item_status=? AND reservation_status IN (?, ?) ORDER BY scheduled_date ASC");
    if($statement===false){
      throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
    }

    $mysqli_checks = $api->bind_params($statement, "sss", array("Reserved", "Pending", "Confirmed"));
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

    $item_count += $api->num_rows($reservations);

    $api->free_result($statement);
    $mysqli_checks = $api->close($statement);
    if($mysqli_checks===false){
      throw new Exception('The prepared statement could not be closed.');
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
  <link href="../style/reservations.css" rel="stylesheet" scoped>
  <!-- native style -->
  <style>
    .Reservations__controls--tab {
      font-size: 1.25rem !important;
      background: rgba(255, 255, 255, 0) !important;
      border-color: #212529 !important;
      border-width: 3px !important;
      padding: 0 0.25rem 0.5rem 0.25rem !important;
      margin: 0 0.5rem !important;
      transition: color .4s;
    }
  </style>
  <title>Bookings | NJC Tattoo</title>
</head>
<body class="w-100">
  <?php require_once '../common/header.php'; ?>
  <div class="Reservations content">
    <div class="Reservations__header">
      <h2 class="fw-bold display-3">Reservations</h2>
      <p class="d-inline fs-5 text-muted">Manage your ongoing worksessions and reservations here.</p>
    </div>
    <div class="Reservations__controls">
      <div>
        <button type="button" class="Reservations__controls--tab border-0 border-bottom text-black" id="Reservations__controls--tab--sessions">Sessions</button>
        <button type="button" class="Reservations__controls--tab border-0 text-muted" id="Reservations__controls--tab--reservations">Reservations</button>
      </div>
      <?php if($item_count > 0){ ?>
        <div>
          <button type="button" class="pb-2 bg-none fs-5 border-0" id="toggle_items">Show All</button>
        </div>
      <?php } ?>
    </div>
    <div id="Reservations--tab--sessions" class="vstack d-flex">
      <?php
        if($api->num_rows($worksessions) > 0){
          while($worksession = $api->fetch_assoc($worksessions)){
            $session_id = $api->sanitize_data($worksession['session_id'], "string");
            $reservation_id = $api->sanitize_data($worksession['reservation_id'], "string");
            $item_id = $api->sanitize_data($worksession['item_id'], "string");
            $first_name = $api->sanitize_data($worksession['client_fname'], "string");
            $last_name = $api->sanitize_data($worksession['client_lname'], "string");
            $user_avatar = $api->sanitize_data($worksession['user_avatar'], "string");
            $mi = $api->sanitize_data($worksession['client_mi'], "string");
            $status = $api->sanitize_data($worksession['session_status'], "string");
            $quantity = $api->sanitize_data($worksession['tattoo_quantity'], "int");
            $addon = number_format($api->sanitize_data($worksession['amount_addon'], "float"), 2, '.', '');
            $tattoo_name = $api->sanitize_data($worksession['tattoo_name'], "string");
            $tattoo_image = $api->sanitize_data($worksession['tattoo_image'], "string");
            $width = $api->sanitize_data($worksession['tattoo_width'], "int");
            $height = $api->sanitize_data($worksession['tattoo_height'], "int");
            $service_type = $api->sanitize_data($worksession['service_type'], "string");
            $session_date = $api->sanitize_data($worksession['session_date'], "string");
            $session_time = $api->sanitize_data($worksession['session_start_time'], "string");
            $address = $api->sanitize_data($worksession['session_address'], "string");
            $description = $api->sanitize_data($worksession['reservation_description'], "string");
      ?>
      <div class="Reservations__item my-2">
        <form action="./scripts/php/queries.php" method="POST">
          <div class="Reservations__item__collapsible collapsible">
            <button type="button" class="Reservations__item__collapsible--toggler col" data-bs-toggle="collapse" data-bs-target="#item_<?php echo $item_id; ?>" aria-expanded="false" aria-controls="item_<?php echo $item_id; ?>">
              <div class="Reservations__item__collapsible__avatar avatar" style="background-image: url(<?php echo $user_avatar; ?>)"></div>
              <h5 class="Reservations__item__collapsible__header">
                <?php
                  $date = date("M:d:Y", strtotime($session_date));
                  $date = explode(':', $date);
                  echo $first_name . " " . $mi . ". " . $last_name . " " . $quantity . " pc. " . $tattoo_name;
                ?>
              </h5>
              <p class="Reservations__item__collapsible__date"><?php echo " on " . $api->sanitize_data($date[0], "string") . " " . $api->sanitize_data($date[1], "int") . ", " . $api->sanitize_data($date[2], "int"); ?></p>
            </button>
            <div class="d-flex w-auto mx-3 pe-5">
              <button type="submit" class="Reservations__controls__control btn btn-primary order-1 me-2" name="finish_worksession" data-bs-toggle="tooltip" data-bs-placement="top" title="Finish Worksession"><span class="Reservations__controls__control__icon material-icons">turned_in</span><span class="Reservations__controls__control__text">Finish</span></button>
            </div>
          </div>
          <div class="collapse mt-3 p-7 Reservations__item__collapsible__body rounded shadow-sm" id="item_<?php echo $item_id; ?>">
            <div class="Reservations__item__collapsible__body--stacking">
              <div class="Reservations__item__collapsible__body__preview order-first">
                <!-- tattoo image -->
                <div class="Reservations__item__collapsible__body__tattoo-preview" style="background-image: url(<?php echo $tattoo_image; ?>)"></div>
              </div>
              <div class="Reservations__item__collapsible__body--stacking__item-details order-last">
                <div class="row">
                  <!-- tattoo name -->
                  <div class="col">
                    <label class="form-label fw-semibold">Applied Item</label>
                    <p><?php echo $tattoo_name; ?></p>
                    <input type="hidden" readonly class="d-none" value="<?php echo $item_id; ?>" name="item_id" />
                  </div>
                  <!-- status -->
                  <div class="col">
                    <label for="status" class="form-label fw-semibold">Status</label>
                    <p class="fw-semibold text-success"><?php echo $status; ?></p>
                    <input type="hidden" readonly class="d-none" value="<?php echo $session_id; ?>" name="session_id" />
                    <input type="hidden" readonly class="d-none" value="<?php echo $reservation_id; ?>" name="reservation_id" />
                  </div>
                  <!-- addon amount -->
                  <div class="col">
                    <label class="form-label fw-semibold">Add-on Amount</label>
                    <p><?php echo $addon; ?></p>
                  </div>
                </div>
                <div class="row">
                  <!-- quantity -->
                  <div class="col">
                    <label for="quantity" class="form-label fw-semibold">Quantity</label>
                    <p><?php echo $quantity . " pc. "; ?></p>
                    <input type="hidden" readonly class="d-none" value="<?php echo $quantity; ?>" name="quantity" />
                  </div>
                  <!-- width -->
                  <div class="col">
                    <label for="width" class="form-label fw-semibold">Width</label>
                    <p><?php echo $width . " in."; ?></p>
                  </div>
                  <!-- height --->
                  <div class="col">
                    <label for="inputAddress" class="form-label fw-semibold">Height</label>
                    <p><?php echo $height . " in."; ?></p>
                  </div>
                </div>
                <div class="row">
                  <!-- service type -->
                  <div class="col">
                    <label for="service_type" class="form-label fw-semibold">Service Type</label>
                    <p><?php echo $service_type; ?></p>
                  </div>
                  <!-- time -->
                  <div class="col">
                    <label for="scheduled_time" class="form-label fw-semibold">Start Time</label>
                    <p><?php echo date("g:i A", strtotime($session_time)); ?></p>
                  </div>
                  <!-- date -->
                  <div class="col">
                    <label for="scheduled_date" class="form-label fw-semibold">Session Date</label>
                    <p><?php echo $api->sanitize_data($date[0], "string") . " " . $api->sanitize_data($date[1], "int") . ", " . $api->sanitize_data($date[2], "int"); ?></p>
                  </div>
                </div>
              </div>
            </div>
            <div class="Reservations__item__collapsible__body__item-details">
              <div class="row mb-5">
                <!-- address -->
                <div class="col">
                  <label for="reservation_address" class="form-label fw-semibold">Address</label>
                  <p><?php echo $address; ?></p>
                </div>
              </div>
              <div class="row mt-5">
                <!-- demands -->
                <div class="col">
                  <label for="reservation_description" class="form-label fw-semibold">Demands</label>
                  <p><?php if(empty($description)){ echo "None"; } else { echo $description; } ?></p>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <?php }} else { ?>
      <h1 class="p-7 display-2 fst-italic text-muted no-select">No ongoing sessions.</h1>
      <?php } ?>
    </div>
    <div id="Reservations--tab--reservations" class="vstack d-none">
      <?php
        if($api->num_rows($reservations) > 0){
          while($reservation = $api->fetch_assoc($reservations)){
            $client_id = $api->sanitize_data($reservation['client_id'], "string");
            $user_avatar = $api->sanitize_data($reservation['user_avatar'], "string");
            $reservation_id = $api->sanitize_data($reservation['reservation_id'], "string");
            $item_id = $api->sanitize_data($reservation['item_id'], "string");
            $first_name = $api->sanitize_data($reservation['client_fname'], "string");
            $last_name = $api->sanitize_data($reservation['client_lname'], "string");
            $mi = $api->sanitize_data($reservation['client_mi'], "string");
            $status = $api->sanitize_data($reservation['reservation_status'], "string");
            $quantity = $api->sanitize_data($reservation['tattoo_quantity'], "int");
            $tattoo_name = $api->sanitize_data($reservation['tattoo_name'], "string");
            $tattoo_image = $api->sanitize_data($reservation['tattoo_image'], "string");
            $paid = $api->sanitize_data($reservation['paid'], "string");
            $addon = number_format($api->sanitize_data($reservation['amount_addon'], "float"), 2, '.', '');
            $width = $api->sanitize_data($reservation['tattoo_width'], "int");
            $height = $api->sanitize_data($reservation['tattoo_height'], "int");
            $service_type = $api->sanitize_data($reservation['service_type'], "string");
            $scheduled_date = $api->sanitize_data($reservation['scheduled_date'], "string");
            $scheduled_time = $api->sanitize_data($reservation['scheduled_time'], "string");
            $address = $api->sanitize_data($reservation['reservation_address'], "string");
            $description = $api->sanitize_data($reservation['reservation_description'], "string");
      ?>
      <div class="Reservations__item my-2">
        <form class="Reservations__item__form" action="./scripts/php/queries.php" method="POST">
          <div class="Reservations__item__collapsible collapsible">
            <button type="button" class="Reservations__item__collapsible--toggler col" data-bs-toggle="collapse" data-bs-target="#item_<?php echo $item_id; ?>" aria-expanded="false" aria-controls="item_<?php echo $item_id; ?>">
              <div class="Reservations__item__collapsible__avatar avatar" style="background-image: url(<?php echo $user_avatar; ?>)"></div>
              <h5 class="Reservations__item__collapsible__header">
                <?php
                  $date = date("M:d:Y", strtotime($scheduled_date));
                  $date = explode(':', $date);
                  echo $first_name . " " . $mi . ". " . $last_name . " " . $quantity . " pc. " . $tattoo_name;
                ?>
              </h5>
              <p class="Reservations__item__collapsible__date"><?php echo " on " . $api->sanitize_data($date[0], "string") . " " . $api->sanitize_data($date[1], "int") . ", " . $api->sanitize_data($date[2], "int"); ?></p>
            </button>
            <div class="d-flex w-auto mx-3 pe-5">
              <button type="submit" class="Reservations__controls__control btn btn-outline-primary order-first me-2" name="update_reservation" data-bs-toggle="tooltip" data-bs-placement="top" title="Update Reservation"><span class="Reservations__controls__control__icon material-icons">edit</span><span class="Reservations__controls__control__text">Update</span></button>
              <?php if(strcasecmp($status, "Confirmed") == 0){ ?>
                <button type="submit" class="Reservations__controls__control btn btn-primary order-1 me-2" name="start_worksession" data-bs-toggle="tooltip" data-bs-placement="top" title="Start Worksession"><span class="Reservations__controls__control__icon material-icons">bookmark_add</span><span class="Reservations__controls__control__text">Start Worksession</span></button>
              <?php } ?>
              <input type="hidden" readonly class="d-none" value="<?php echo $client_id; ?>" name="client_id" />
              <button type="submit" class="Reservations__controls__control btn btn-outline-danger order-last" name="cancel_reservation" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel Reservation"><span class="Reservations__controls__control__icon material-icons">bookmark_remove</span><span class="Reservations__controls__control__text">Cancel</span></button>
            </div>
          </div>
          <div class="Reservations__item__collapsible__body collapse mt-3 p-7 rounded shadow-sm" id="item_<?php echo $item_id; ?>">
            <div class="Reservations__item__collapsible__body--stacking">
              <div class="Reservations__item__collapsible__body__preview order-first">
                <!-- tattoo image -->
                <div class="Reservations__item__collapsible__body__tattoo-preview" style="background-image: url(<?php echo $tattoo_image; ?>)"></div>
              </div>
              <div class="Reservations__item__collapsible__body--stacking__item-details order-last">
                <div class="row">
                  <!-- tattoo name -->
                  <div class="col">
                    <label class="form-label fw-semibold">Reserved Item</label>
                    <p><?php echo $tattoo_name; ?></p>
                    <input type="hidden" readonly class="d-none" value="<?php echo $item_id; ?>" name="item_id" />
                  </div>
                  <!-- status -->
                  <div class="col">
                    <label for="status" class="form-label fw-semibold">Status</label>
                    <div class="fw-semibold"><p class="d-inline <?php if(strcasecmp($status, "Confirmed") == 0){ echo "text-success"; } else { echo "text-secondary"; } ?>"><?php echo $status . ", "; ?></p><p class="d-inline <?php if(strcasecmp($paid, "Fully Paid") == 0){ echo "text-success"; } else { echo "text-secondary"; } ?>"><?php echo $paid; ?></p></div>
                    <input type="hidden" readonly class="d-none" value="<?php echo $reservation_id; ?>" name="reservation_id" />
                  </div>
                  <!-- addon amount -->
                  <div class="col">
                    <label class="form-label fw-semibold">Add-on Amount</label>
                    <?php if(strcasecmp($paid, 'Fully Paid') == 0){ ?>
                      <p>₱<?php echo number_format($addon, 2, '.', ''); ?></p>
                    <?php } else { ?>
                      <div class="input-group">
                        <?php if(strcasecmp($paid, "Fully Paid") != 0){ ?>
                          <span class="input-group-text">₱</span>
                        <?php } ?>
                        <input type="<?php echo (strcasecmp($paid, 'Fully Paid') == 0) ? "hidden" : "number" ?>"<?php if(strcasecmp($paid, 'Fully Paid') == 0){ echo " readonly"; } ?> class="<?php echo (strcasecmp($paid, 'Fully Paid') == 0) ? "d-none" : "form-control" ?>" value="<?php echo $addon; ?>" name="amount_addon" />
                      </div>
                    <?php } ?>
                  </div>
                </div>
                <div class="row">
                  <!-- quantity -->
                  <div class="col">
                    <label for="quantity" class="form-label fw-semibold">Quantity</label>
                    <p><?php echo $quantity . " pc. "; ?></p>
                    <input type="hidden" readonly class="d-none" value="<?php echo $quantity; ?>" name="quantity" />
                  </div>
                  <!-- width -->
                  <div class="col">
                    <label for="width" class="form-label fw-semibold">Width</label>
                    <p><?php echo $width . " in."; ?></p>
                  </div>
                  <!-- height --->
                  <div class="col">
                    <label for="inputAddress" class="form-label fw-semibold">Height</label>
                    <p><?php echo $height . " in."; ?></p>
                  </div>
                </div>
                <div class="row">
                  <!-- service type -->
                  <div class="col">
                    <label for="service_type" class="form-label fw-semibold">Service Type</label>
                    <select name="service_type" class="form-select form-select-md mb-3">
                      <option value="Walk-in" <?php if(strcasecmp($service_type, 'Walk-in') == 0){ echo "selected"; } ?>>Walk-in</option>
                      <option value="Home Service" <?php if(strcasecmp($service_type, 'Walk-in') != 0){ echo "selected"; } ?>>Home Service</option>
                    </select>
                  </div>
                  <!-- time -->
                  <div class="col">
                    <label for="scheduled_time" class="form-label fw-semibold">Scheduled Time</label>
                    <input type="time" class="form-control" value="<?php echo $scheduled_time; ?>" name="scheduled_time" />
                    <label class="error-message scheduled_time_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span><span></span></label>
                  </div>
                  <!-- date -->
                  <div class="col">
                    <label for="scheduled_date" class="form-label fw-semibold">Scheduled Date</label>
                    <input type="date" class="form-control" value="<?php echo $scheduled_date; ?>" name="scheduled_date" />
                    <label class="error-message scheduled_date_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span><span></span></label>
                  </div>
                </div>
              </div>
            </div>
            <div class="Reservations__item__collapsible__body__item-details">
              <div class="row mb-5">
                <!-- address -->
                <div class="col">
                  <label for="reservation_address" class="form-label fw-semibold">Address</label>
                  <input type="text" class="form-control" value="<?php echo $address; ?>" name="reservation_address" />
                  <label class="error-message reservation_address_err d-none"><span class="material-icons-outlined fs-6 me-1">info</span><span></span></label>
                </div>
              </div>
              <div class="row mt-5">
                <!-- demands -->
                <div class="col">
                  <label for="reservation_description" class="form-label fw-semibold">Demands</label>
                  <p><?php if(empty($description)){ echo "None"; } else { echo $description; } ?></p>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <?php }} else { ?>
        <h1 class="p-7 display-2 fst-italic text-muted no-select">No ongoing reservations.</h1>
      <?php } ?>
    </div>
  </div>  
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script src="../api/api.js"></script>
<script>
  // tabs
  var sessions_tab = document.getElementById('Reservations__controls--tab--sessions');
  var reservations_tab = document.getElementById('Reservations__controls--tab--reservations');

  // tab sections
  var sessions = document.getElementById('Reservations--tab--sessions');
  var reservations = document.getElementById('Reservations--tab--reservations');

  sessions_tab.addEventListener('click', function(){
    this.className = "Reservations__controls--tab border-0 border-bottom text-black";
    reservations_tab.className = "Reservations__controls--tab border-0 text-muted";

    sessions.classList.replace('d-none', 'd-flex');
    reservations.classList.replace('d-flex', 'd-none');
  });

  reservations_tab.addEventListener('click', function(){
    this.className = "Reservations__controls--tab border-0 border-bottom text-black";
    sessions_tab.className = "Reservations__controls--tab border-0 text-muted";

    reservations.classList.replace('d-none', 'd-flex');
    sessions.classList.replace('d-flex', 'd-none');
  });
</script>
<?php if($item_count > 0){ ?>
  <script src="./scripts/js/reservations.js"></script>
<?php } ?>
</html>