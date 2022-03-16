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
    $item_count = 0;

    // retrieving ongoing worksessions
    $statement = $api->prepare("SELECT client_fname, client_mi, client_lname, worksession.reservation_id, session_id, session_status, reservation.item_id, tattoo_name, tattoo_image, tattoo_quantity, order_item.tattoo_width, order_item.tattoo_height, service_type, amount_addon, session_date, session_start_time, session_address, reservation_description FROM (((((workorder INNER JOIN order_item ON workorder.order_id=order_item.order_id) INNER JOIN client ON workorder.client_id=client.client_id) INNER JOIN reservation ON reservation.item_id=order_item.item_id) INNER JOIN worksession ON reservation.reservation_id=worksession.reservation_id) INNER JOIN tattoo ON tattoo.tattoo_id=order_item.tattoo_id) WHERE session_status!= ? ORDER BY session_date ASC");
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
    if($mysqli_checks===false){
        throw new Exception('The prepared statement could not be closed.');
    } else {
      $statement = null;
    }

    // retrieving ongoing reservations
    $statement = $api->prepare("SELECT workorder.client_id, client_fname, client_mi, client_lname, reservation_id, reservation_status, reservation.item_id, tattoo_name, tattoo_image, tattoo_quantity, paid, order_item.tattoo_width, order_item.tattoo_height, service_type, amount_addon, scheduled_date, scheduled_time, reservation_address, reservation_description FROM ((((workorder INNER JOIN order_item ON workorder.order_id=order_item.order_id) INNER JOIN client ON workorder.client_id=client.client_id) INNER JOIN reservation ON reservation.item_id=order_item.item_id) INNER JOIN tattoo ON tattoo.tattoo_id=order_item.tattoo_id) WHERE reservation_id NOT IN (SELECT reservation_id FROM worksession) AND item_status=? AND reservation_status IN (?, ?) ORDER BY scheduled_date ASC");
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
      exit();
      $_SESSION['res'] = $e->getMessage();
      Header("Location: ./index.php");
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require_once '../common/meta.php'; ?>
  <!-- native style -->
  <style>
    .item {
      transition: margin .35s;
    }

    .tattoo-image {
      max-width: 400px;
      max-height: 400px;
      width: 400px;
      height: 4000px;
      background-position: center;
      background-repeat: no-repeat;
      background-size: cover;
    }
  </style>
  <title>Bookings | NJC Tattoo</title>
</head>
<body class="w-100">
  <header class="header border-bottom border-2">
    <nav class="nav-bar row mx-0">
      <ul class="col my-0" id="nav-links">
        <li><a href="catalogue.php">Catalogue</a></li>
        <li><a href="orders.php">Orders</a></li>
        <li class="active"><a href="reservations.php">Bookings</a></li>
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
      <h2 class="fw-bold display-3">Reservations</h2>
      <p class="d-inline fs-5 text-muted">Manage your ongoing worksessions and reservations here.</p>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
      <div>
        <button type="button" class="tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-x-0 border-top-0 text-black" id="sessions-tab">Sessions</button>
        <button type="button" class="tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-0 text-muted" id="reservations-tab">Reservations</button>
      </div>
      <?php if($item_count > 0){ ?>
        <div>
          <button type="button" class="pb-2 bg-none fs-5 border-0" id="toggle_items">Show All</button>
        </div>
      <?php } ?>
    </div>
    <div id="sessions" class="vstack">
      <?php
        if($api->num_rows($worksessions) > 0){
          while($worksession = $api->fetch_assoc($worksessions)){
            $session_id = $api->sanitize_data($worksession['session_id'], "string");
            $reservation_id = $api->sanitize_data($worksession['reservation_id'], "string");
            $item_id = $api->sanitize_data($worksession['item_id'], "string");
            $first_name = $api->sanitize_data($worksession['client_fname'], "string");
            $last_name = $api->sanitize_data($worksession['client_lname'], "string");
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
      <div class="item border shadow-sm">
        <form action="./queries.php" method="POST">
          <div class="collapsible d-flex align-items-center justify-content-between">
            <button type="button" class="col bg-none text-start border-0" style="padding: 2rem;" data-bs-toggle="collapse" data-bs-target="#item_<?php echo $item_id; ?>" aria-expanded="false" aria-controls="item_<?php echo $item_id; ?>">
            <h5 class="d-inline">
              <?php
                $date = date("M:d:Y", strtotime($session_date));
                $date = explode(':', $date);
                echo $first_name . " " . $mi . ". " . $last_name . " " . $quantity . " pc. " . $tattoo_name;
              ?>
            </h5><p class="d-inline text-muted"><?php echo " on " . $api->sanitize_data($date[0], "string") . " " . $api->sanitize_data($date[1], "int") . ", " . $api->sanitize_data($date[2], "int"); ?></p></button>
            <div class="w-auto mx-3">
              <button type="submit" class="order-1 btn btn-primary" name="finish_worksession">Finish Worksession</button>
            </div>
          </div>
          <div class="collapse border-top p-7 item_collapsible" id="item_<?php echo $item_id; ?>">
            <div class="mt-3">
              <div class="d-flex align-items-center justify-content-between">
                <!-- tattoo image -->
                <div>
                  <div class="tattoo-image shadow-sm border-2 rounded-pill" style="background-image: url(<?php echo $tattoo_image; ?>)"></div>
                </div>
                <div class="w-100 ms-6">
                  <div class="row my-5">
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
                  <div class="row my-5">
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
                  <div class="row my-5">
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
              <div class="row my-5">
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
      <?php
        }
      } else {
      ?>
      <h1 class="p-7 display-2 fst-italic text-muted no-select">No ongoing sessions.</h1>
      <?php
        }
      ?>
    </div>
    <div id="reservations" class="vstack d-none">
      <?php
        if($api->num_rows($reservations) > 0){
          while($reservation = $api->fetch_assoc($reservations)){
            $client_id = $api->sanitize_data($reservation['client_id'], "string");
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
      <div class="item border shadow-sm">
        <form action="./queries.php" method="POST">
          <div class="collapsible d-flex align-items-center justify-content-between">
            <button type="button" class="col bg-none text-start border-0" style="padding: 2rem;" data-bs-toggle="collapse" data-bs-target="#item_<?php echo $item_id; ?>" aria-expanded="false" aria-controls="item_<?php echo $item_id; ?>">
            <h5 class="d-inline">
              <?php
                $date = date("M:d:Y", strtotime($scheduled_date));
                $date = explode(':', $date);
                echo $first_name . " " . $mi . ". " . $last_name . " " . $quantity . " pc. " . $tattoo_name;
              ?>
            </h5><p class="d-inline text-muted"><?php echo " on " . $api->sanitize_data($date[0], "string") . " " . $api->sanitize_data($date[1], "int") . ", " . $api->sanitize_data($date[2], "int"); ?></p></button>
            <div class="w-auto mx-3">
              <button type="submit" class="order-0 btn btn-outline-primary" name="update_reservation">Update</button>
              <?php if(strcasecmp($status, "Confirmed") == 0){?>
                <button type="submit" class="order-1 btn btn-primary" name="start_worksession">Start Worksession</button>
              <?php } ?>
              <input type="hidden" readonly class="d-none" value="<?php echo $client_id; ?>" name="client_id" />
              <button type="submit" class="order-2 btn btn-outline-danger" name="cancel_reservation">Cancel Reservation</button>
            </div>
          </div>
          <div class="collapse border-top p-7 item_collapsible" id="item_<?php echo $item_id; ?>">
            <div class="mt-3">
              <div class="d-flex align-items-center justify-content-between">
                <!-- tattoo image -->
                <div>
                  <div class="tattoo-image shadow-sm border-2 rounded-pill" style="background-image: url(<?php echo $tattoo_image; ?>)"></div>
                </div>
                <div class="w-100 ms-6">
                  <div class="row my-5">
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
                        <p><?php echo $addon; ?></p>
                      <?php } ?>
                      <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="<?php echo (strcasecmp($paid, 'Fully Paid') == 0) ? "hidden" : "number" ?>"<?php if(strcasecmp($paid, 'Fully Paid') == 0){ echo " readonly"; } ?> class="<?php echo (strcasecmp($paid, 'Fully Paid') == 0) ? "d-none" : "form-control" ?>" value="<?php echo $addon; ?>" name="amount_addon" />
                      </div>
                    </div>
                  </div>
                  <div class="row my-5">
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
                  <div class="row my-5">
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
                    </div>
                    <!-- date -->
                    <div class="col">
                      <label for="scheduled_date" class="form-label fw-semibold">Scheduled Date</label>
                      <input type="date" class="form-control" value="<?php echo $scheduled_date; ?>" name="scheduled_date" />
                    </div>
                  </div>
                </div>
              </div>
              <div class="row my-5">
                <!-- address -->
                <div class="col">
                  <label for="reservation_address" class="form-label fw-semibold">Address</label>
                  <input type="text" class="form-control" value="<?php echo $address; ?>" name="reservation_address" />
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
      <?php
        }
      } else {
      ?>
      <h1 class="p-7 display-2 fst-italic text-muted no-select">No ongoing reservations.</h1>
      <?php
        }
      ?>
    </div>
  </div>  
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script>
  // tabs
  var sessions_tab = document.getElementById('sessions-tab');
  var reservations_tab = document.getElementById('reservations-tab');

  // tab sections
  var sessions = document.getElementById('sessions');
  var reservations = document.getElementById('reservations');

  sessions_tab.addEventListener('click', function(){
    this.className = "tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-x-0 border-top-0 text-black";
    reservations_tab.className = "tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-0 text-muted";

    sessions.classList.remove('d-none');
    sessions.classList.add('d-flex');

    reservations.classList.remove('d-flex');
    reservations.classList.add('d-none');
  });

  reservations_tab.addEventListener('click', function(){
    this.className = "tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-x-0 border-top-0 text-black";
    sessions_tab.className = "tabs mx-2 pb-2 px-1 bg-none border-dark border-3 fs-5 border-0 text-muted";

    reservations.classList.remove('d-none');
    reservations.classList.add('d-flex');

    sessions.classList.remove('d-flex');
    sessions.classList.add('d-none');
  });
</script>
<?php if($item_count > 0){ ?>
  <script>
    // collapsible toggling
    var show_all_items = false;
    var toggle_items = document.getElementById('toggle_items');

    // collapsible
    var items = document.getElementsByClassName('item');
    var item_collapsibles = document.getElementsByClassName('item_collapsible');

    // collapsibles stateful styling
  for(var i=0, count=items.length; i < count; i++){
    let item = items[i];

    item_collapsibles[i].addEventListener('shown.bs.collapse', function (){
      item.classList.add('my-4');
    });

    item_collapsibles[i].addEventListener('hidden.bs.collapse', function (){
      item.classList.remove('my-4');
    });
  }

  // toggling all collapsibles
  toggle_items.addEventListener('click', function(){
    show_all_items = !show_all_items;
    show_all_items === true ? toggle_items.innerText = "Hide All" : toggle_items.innerText = "Show All";
    
    for(var i=0, count=item_collapsibles.length; i < count; i++){
      if(show_all_items === true){
        if(!(item_collapsibles[i].classList.contains('show'))){
          let collapse = new bootstrap.Collapse(item_collapsibles[i], { show: true, hide: false });
        }
      } else {
        if((item_collapsibles[i].classList.contains('show'))){
          let collapse = new bootstrap.Collapse(item_collapsibles[i], { show: false, hide: true });
        }
      }
    }
  });
  </script>
<?php } ?>
</html>