<?php
  session_name("sess_id");
  session_start();
  if(!isset($_SESSION['user']['user_id']) || strcasecmp($_SESSION['user']['user_type'], "User") != 0){
    Header("Location: ./index.php");
    die();
  } else {
    require_once '../api/api.php';
    $api = new api();
  }
  
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

    // get all standing order items
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
  <?php require_once '../common/meta.php'; ?>
  <link href="../style/reservations.css" rel="stylesheet" scoped>
  <title>Bookings | NJC Tattoo</title>
</head>
<body class="w-100">
  <?php require_once '../common/header.php'; ?>
  <div class="Reservations content">
    <div class="Reservations__header">
      <h2 class="fw-bold display-3">Reservations</h2>
      <p class="d-inline fs-5 text-muted">Make reservations for your tattoo orders and manage your ongoing reservations here.</p>
    </div>
    <div class="Reservations__controls">
      <?php if($api->num_rows($reservations) > 0){ ?>
        <button type="button" class="btn btn-link btn-lg text-black text-decoration-none me-1" id="toggle_reservations">Show All Reservations</button>
      <?php } ?>
      <div class="d-flex justify-content-between align-items-center">
        <?php if($api->num_rows($reservations) > 0){ ?>
          <div class="me-3 form-check form-switch">
            <input class="form-check-input" type="checkbox" id="edit_reservations" />
            <label class="form-check-label" for="edit_reservations" id="edit_reservations_label">Edit</label>
          </div>
        <?php } if(!empty($order_id)){ ?>
          <button type="submit" class="Reservations__controls__control btn btn-outline-dark me-2" data-bs-toggle="collapse" data-bs-target="#new_reservation" aria-expanded="false" aria-controls="new_reservation" title="New Reservation"><span class="Reservations__controls__control__icon material-icons">bookmark_add</span><span class="Reservations__controls__control__text">New Reservation</span></button>
        <?php } ?>
      </div>
    </div>
    <div class="collapse border-bottom rounded mt-3 mb-7 py-7" id="new_reservation">
      <form action="./new_reservation.php" method="post">
        <label for="item" class="form-label text-muted">Reservation Item</label>
        <div class="input-group">
          <select class="form-select form-select-lg" name="item">
            <?php
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
            <button type="submit" class="btn btn-outline-dark d-flex align-items-center"><span class="material-icons md-48 lh-base">add</span>New Reservation</button>
          <?php } ?>
        </div>
      </form>
    </div>
    <?php if($api->num_rows($reservations) > 0){ ?>
      <div class="reservations vstack">
        <?php
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
          <div class="Reservations__item my-2">
            <div class="Reservations__item__collapsible collapsible">
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
            <div class="Reservations__item__collapsible__body collapse mt-3 p-7 rounded shadow-sm" id="item_<?php echo $item_id; ?>">
              <form action="../scripts/php/queries.php" method="POST">
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
                        <div class="fw-semibold"><p class="d-inline <?php if(strcasecmp($status, "Confirmed") == 0){ echo "text-success"; } else { echo "text-secondary"; } ?>"><?php echo $status; ?></p>, <p class="d-inline <?php if(strcasecmp($paid, "Fully Paid") == 0){ echo "text-success"; } else { echo "text-secondary"; } ?>"><?php echo $paid; ?></p></div>
                        <input type="hidden" readonly class="d-none" value="<?php echo $reservation_id; ?>" name="reservation_id" />
                      </div>
                      <!-- addon amount -->
                      <div class="col">
                        <label class="form-label fw-semibold">Add-on Amount</label>
                        <p>₱<?php echo $addon; ?></p>
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
                        <label for="height" class="form-label fw-semibold">Height</label>
                        <p><?php echo $height . " in."; ?></p>
                      </div>
                    </div>
                    <div class="row">
                      <!-- service type -->
                      <div class="col">
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
                      <div class="col">
                        <label for="scheduled_time" class="form-label fw-semibold">Scheduled Time</label>
                        <?php if(strcasecmp($status, "Confirmed") == 0){ ?>
                          <p><?php echo $api->sanitize_data(date("g:i A", strtotime($scheduled_time)), 'string'); ?></p>
                        <?php } ?>
                        <input type="<?php echo strcasecmp($status, "Confirmed") == 0 ? "hidden" : "time"; ?>" readonly class="<?php if(strcasecmp($status, "Pending") == 0){ echo "reservations "; } ?>form-control" value="<?php echo $scheduled_time; ?>" name="scheduled_time" />
                      </div>
                      <!-- date -->
                      <div class="col">
                        <label for="scheduled_date" class="form-label fw-semibold">Scheduled Date</label>
                        <?php if(strcasecmp($status, "Confirmed") == 0){ ?>
                          <p><?php echo $api->sanitize_data($date[0], 'string') . " " . $api->sanitize_data($date[1], 'int') . ", " . $api->sanitize_data($date[2], 'int'); ?></p>
                        <?php } ?>
                        <input type="<?php echo strcasecmp($status, "Confirmed") == 0 ? "hidden" : "date"; ?>" readonly class="<?php if(strcasecmp($status, "Pending") == 0){ echo "reservations "; } ?>form-control" value="<?php echo $scheduled_date; ?>" name="scheduled_date" />
                      </div>
                    </div>
                  </div>
                </div>
                <div class="Reservations__item__collapsible__body__item-details">
                  <div class="row mb-5">
                    <!-- address -->
                    <div class="col">
                      <label for="reservation_address" class="form-label fw-semibold">Address</label>
                      <?php if(strcasecmp($status, "Confirmed") == 0){ ?>
                        <p><?php echo $address; ?></p>
                      <?php } ?>
                      <input type="<?php echo strcasecmp($status, "Confirmed") == 0 ? "hidden" : "text"; ?>" readonly class="<?php echo strcasecmp($status, "Pending") == 0 ? "reservations form-control" : "d-none"; ?>" value="<?php echo $address; ?>" name="reservation_address" />
                    </div>
                  </div>
                  <div class="row <?php echo strcasecmp($status, 'Confirmed') == 0 ? "mt-5" : "my-5"; ?>">
                    <!-- demands -->
                    <div class="col">
                      <label for="reservation_description" class="form-label fw-semibold">Demands</label>
                      <?php if(strcasecmp($status, "Confirmed") == 0){ ?>
                        <p><?php echo $description; ?></p>
                      <?php } else { ?>
                        <textarea readonly class="<?php if(strcasecmp($status, "Pending") == 0){ echo "reservations "; } ?>form-control p-3 text-wrap" name="reservation_demands" rows="5" placeholder="Reservation Demands"><?php echo $description; ?></textarea>
                        <p class="my-2 d-none text-danger"></p>
                      <?php } ?>
                    </div>
                  </div>
                </div>
                <?php if(strcasecmp($status, 'Pending') == 0){ ?>
                  <div class="row mt-1 mb-2">
                    <div class="col d-flex justify-content-end">
                      <button type="submit" class="order-0 btn btn-secondary d-none" name="update_reservation">Update</button>
                      <button type="submit" class="order-1 ms-1 btn btn-primary" name="confirm_reservation">Confirm Reservation</button>
                      <?php if(strcasecmp($paid, 'Fully Paid') != 0){ ?>
                        <button type="submit" class="order-2 ms-1 btn btn-outline-danger" name="cancel_reservation">Cancel</button>
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
  <script>
    // editing reservations
    var edit_reservations = document.getElementById('edit_reservations');
    var edit_reservations_label = document.getElementById('edit_reservations_label');

    // reservation fields
    var update_buttons = document.getElementsByName('update_reservation');
    var reservation_row_fields = document.querySelectorAll(".reservations.form-control");
    var reservation_selects = document.querySelectorAll(".reservations.form-select");

    // collapsible toggling
    var show_reservations = false;
    var toggle_reservations = document.getElementById('toggle_reservations');

    // collapsibles
    var reservations = document.getElementsByClassName('Reservations__item');
    var reservations_collapsibles = document.getElementsByClassName('Reservations__item__collapsible__body');
    
    // collapsibles stateful styling
    for(var i=0, count=reservations.length; i < count; i++){
      reservations[i].addEventListener('shown.bs.collapse', function (){
        this.classList.add('mb-5');
      });

      reservations[i].addEventListener('hidden.bs.collapse', function (){
        this.classList.remove('mb-5');
      });
    }

    // toggle collapsibles
    toggle_reservations.addEventListener('click', function(){
      show_reservations = !show_reservations;
      show_reservations === true ? toggle_reservations.innerText = "Hide All Reservations" : toggle_reservations.innerText = "Show All Reservations";
      
      for(var i=0, count=reservations_collapsibles.length; i < count; i++){
        if(show_reservations === true){
          if(!(reservations_collapsibles[i].classList.contains('show'))){
            let collapse = new bootstrap.Collapse(reservations_collapsibles[i], { show: true, hide: false });
          }
        } else {
          if((reservations_collapsibles[i].classList.contains('show'))){
            let collapse = new bootstrap.Collapse(reservations_collapsibles[i], { show: false, hide: true });
          }
        }
      }
    });

    // toggling reservation editing
    edit_reservations.addEventListener('click', function(){
        this.checked ? edit_reservations_label.innerText = "Stop Editing" : edit_reservations_label.innerText = "Edit";
        
        for(var i=0, count=reservation_row_fields.length; i < count; i++){
          reservation_row_fields[i].readOnly = this.checked ? false : true;
        }

        for(var i=0, count=reservation_selects.length; i < count; i++){
          if(this.checked){
            reservation_selects[i].classList.remove('no-select');
            reservation_selects[i].classList.remove('form-select-plaintext');

            for(var j = 0; j < reservation_selects[i].options.length; j++){
              reservation_selects[i].options[j].disabled = false;
            }
          } else {
            reservation_selects[i].classList.add('no-select');
            reservation_selects[i].classList.add('form-select-plaintext');

            for(var j = 0; j < reservation_selects[i].options.length; j++){
              if(!reservation_selects[i].options[j].selected){
                reservation_selects[i].options[j].disabled = true;
              }
            }
          }
        }

        for(var j=0, count=update_buttons.length; j < count; j++){
          if(this.checked){
            update_buttons[j].classList.remove('d-none');
            update_buttons[j].classList.add('d-inline');
          } else {
            update_buttons[j].classList.add('d-none');
            update_buttons[j].classList.remove('d-inline');
          }
        }
    });
  </script>
<?php } ?>
</html>