<?php
  session_name("sess_id");
  session_start();
  if(!isset($_SESSION['user_id'])){
    Header("Location: ./index.php");
    die();
  } else {
    require_once '../api/api.php';
    $api = new api();
    $client_id = $_SESSION['client_id'];
  }

  if(!isset($_SESSION['order_id']) || empty($_SESSION['order_id'])){
    try {
      $client_id = $_SESSION['client_id'];
      $mysqli_checks = $api->get_workorder($client_id);
      if ($mysqli_checks!==true) {
        throw new Exception('Error: Retrieving client workorder failed.');
      }
    } catch (Exception $e) {
      exit();
      $_SESSION['res'] = $e->getMessage();
      Header("Location: ./index.php");
    }
  }

  $order_id = $_SESSION['order_id'];
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
    .tattoo-image {
      max-width: 400px;
      max-height: 400px;
      width: 400px;
      height: 400px;
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
        <li><a href="explore.php">Explore</a></li>
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
  <div class="w-80 my-9 mx-auto">
    <button type="button" class="collapsible p-4" data-bs-toggle="collapse" data-bs-target="#new_res" aria-expanded="false" aria-controls="new_res"><h5><span class="material-icons md-48 lh-base">add</span>New Reservation</h5></button>
    <div class="collapse p-4 border-top-0" id="new_res">
      <form class="w-100 d-flex align-items-center justify-content-around flex-row" action="new_reservation.php" method="post">
        <?php
          try {
            $left = $api->join("", "tattoo", "order_item", "tattoo.tattoo_id", "order_item.tattoo_id");
            $join = $api->join("", $left, "workorder", "order_item.order_id", "workorder.order_id");

            // get all standing order items
            $get_order_items = $api->select();
            $get_order_items = $api->params($get_order_items, array("item_id", "paid", "tattoo_name", "tattoo_quantity"));
            $get_order_items = $api->from($get_order_items);
            $get_order_items = $api->table($get_order_items, $join);
            $get_order_items = $api->where($get_order_items, array("client_id", "item_status"), array("?", "?"));

            $condition = "AND tattoo_quantity != ?";
            $get_order_items = $get_order_items . $condition;
      
            $statement = $api->prepare($get_order_items);
            if ($statement===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }
        
            $mysqli_checks = $api->bind_params($statement, "ssi", array($client_id, "Standing", 0));
            if ($mysqli_checks===false) {
                throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
            }
        
            $mysqli_checks = $api->execute($statement);
            if($mysqli_checks===false) {
                throw new Exception('Execute error: The prepared statement could not be executed.');
            } else {
              $left = null;
              $join = null;
            }

            $res = $api->get_result($statement);
            if($res===false){
              throw new Exception('get_result() error: Getting result set from statement failed.');
            }
          } catch (Exception $e) {
              exit();
              $_SESSION['res'] = $e->getMessage();
              Header("Location: ../client/index.php");
          }
        ?>
        <select name="item" class="w-80 form-select form-select-lg" required>
          <option value="" class="d-none" hidden selected>Select Item</option>
          <?php
            if($api->num_rows($res) > 0){
              while($row = $api->fetch_assoc($res)){
          ?>
          <option value="<?php echo $row['item_id'] ?>">
            <?php
              echo $row['tattoo_quantity'] . " pc. ". $row['tattoo_name'];
              if(strcasecmp($row['paid'], "Partially Paid") == 0) { echo " (Paid)"; } else { echo " (Unpaid)"; }
            ?>
          </option>
          <?php
              }
          ?>
        </select>
          <button type="submit" class="btn btn-lg btn-outline-dark" name="submit"><span class="material-icons md-48 lh-base">add</span>New Reservation</button>
          <?php       
            } else {
          ?>
          <option value="" selected>No items available for resevation.</option>
          <?php
            }
          ?>
        </select>
      </form> 
    </div>
  </div>
  <?php
    try {
      $api->free_result($statement);
  
      $mysqli_checks = $api->close($statement);
      if ($mysqli_checks===false) {
          throw new Exception('The prepared statement could not be closed.');
      } else {
        $statement = null;
        $res = null;
      }
    } catch (Exception $e) {
      echo $e->getMessage();
      Header("Location: ./index.php");
    }

    $left = $api->join("INNER", "reservation", "order_item", "reservation.item_id", "order_item.item_id");
    $right = $api->join("INNER", $left, "tattoo", "tattoo.tattoo_id", "order_item.tattoo_id");
    $join = $api->join("LEFT", $right, "worksession", "worksession.reservation_id", "reservation.reservation_id");

    $query = $api->select();
    $query = $api->params($query, array("reservation.reservation_id", "reservation_status", "reservation.item_id", "tattoo_name", "tattoo_image", "tattoo_quantity", "order_item.tattoo_width", "order_item.tattoo_height", "service_type", "scheduled_date", "scheduled_time", "reservation_address", "reservation_description", "amount_addon"));
    $query = $api->from($query);
    $query = $api->table($query, $join);
    $query = $api->where($query, "item_status", "?");

    $con = "AND reservation_status IN (?, ?) AND session_id IS NULL ";
    $query = $query . $con;
    $query = $api->order($query, array("scheduled_date", "scheduled_time", "reservation_status"), array("ASC", "ASC", "ASC"));

    try {
      $statement = $api->prepare($query);
      if ($statement===false) {
          throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
      }
  
      $mysqli_checks = $api->bind_params($statement, "sss", array("Reserved", "Pending", "Confirmed"));
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
      Header("Location: ../client/index.php");
  }
  ?>
  <div class="w-80 mx-auto">
    <div class="row">
      <h2 class="col fw-bold display-6">Ongoing Reservations</h2>
      <?php
        if($api->num_rows($res) > 0){
      ?>
      <div class="col d-flex align-items-center justify-content-end fs-5">
        <button type="button" class="border-0 bg-none mx-4" data-bs-toggle="collapse" data-bs-target=".reservation" aria-expanded="false" aria-controls="reservation"><h4 class="mb-0">Toggle Reservations</h4></button>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="edit_reservations" />
          <label class="form-check-label" for="edit_reservations" id="edit_reservations_label">Edit</label>
        </div>
      </div>
      <?php
        }
      ?>
    </div>
    <div class="my-5 vstack">
      <?php
      if($api->num_rows($res) > 0){
        while($row = $api->fetch_assoc($res)){
      ?>
      <div class="my-4 shadow-sm">
        <button type="button" class="collapsible p-4" data-bs-toggle="collapse" data-bs-target="#item_<?php echo $api->clean($row['item_id']) ?>" aria-expanded="true" aria-controls="item_<?php echo $api->clean($row['item_id']) ?>"><h5 class="d-inline">
            <?php
              $date = date("M:d:Y", strtotime($api->clean($row['scheduled_date'])));
              $date = explode(':', $date);
              echo $api->clean($row['tattoo_quantity']) . " pc. " . $api->clean($row['tattoo_name']);
            ?>
          </h5><p class="d-inline text-muted"><?php echo " on " . $api->clean($date[0]) . " " . $api->clean($date[1]) . ", " . $api->clean($date[2]) ?></p></button>
        <div class="collapse border-top-0 p-5 reservation" id="item_<?php echo $api->clean($row['item_id']) ?>">
          <form action="../api/queries.php" method="POST">
            <div class="mt-3">
              <div class="d-flex align-items-center justify-content-between">
                <!-- tattoo image -->
                <div class="tattoo-image shadow-sm border-2 rounded" style="background-image: url(<?php echo $api->clean($row['tattoo_image']); ?>)"></div>
                <div class="w-100 ms-6">
                  <div class="row my-5">
                    <!-- tattoo name -->
                    <div class="col">
                      <label class="form-label fw-semibold">Reserved Item</label>
                      <p><?php echo $api->clean($row['tattoo_name']) ?></p>
                      <input type="hidden" readonly class="d-none" value="<?php echo $api->clean($row['item_id']) ?>" name="item_id" />
                    </div>
                    <!-- status -->
                    <div class="col">
                      <label for="status" class="form-label fw-semibold">Status</label>
                      <p class="fw-semibold <?php echo strcasecmp($row['reservation_status'], "Confirmed") == 0 ? "text-success" : "text-secondary"; ?>"><?php echo $api->clean($row['reservation_status']) ?></p>
                      <input type="hidden" readonly class="d-none" value="<?php echo $api->clean($row['reservation_id']) ?>" name="reservation_id" />
                    </div>
                    <!-- addon amount -->
                    <div class="col">
                      <label class="form-label fw-semibold">Add-on Amount</label>
                      <p>Php <?php echo $api->clean($row['amount_addon']) ?></p>
                    </div>
                  </div>
                  <div class="row my-5">
                    <!-- quantity -->
                    <div class="col">
                      <label for="quantity" class="form-label fw-semibold">Quantity</label>
                      <p><?php echo $api->clean($row['tattoo_quantity']) . " pc. "?></p>
                      <input type="hidden" readonly class="d-none" value="<?php echo $api->clean($row['tattoo_quantity']) ?>" name="quantity" />
                    </div>
                    <!-- width -->
                    <div class="col">
                      <label for="width" class="form-label fw-semibold">Width</label>
                      <p><?php echo $api->clean($row['tattoo_width']) . " in." ?></p>
                    </div>
                    <!-- height --->
                    <div class="col">
                      <label for="height" class="form-label fw-semibold">Height</label>
                      <p><?php echo $api->clean($row['tattoo_height']) . " in." ?></p>
                    </div>
                  </div>
                  <div class="row my-5">
                    <!-- service type -->
                    <div class="col">
                      <label for="service_type" class="form-label fw-semibold">Service Type</label>
                      <?php if(strcasecmp($row['reservation_status'], "Confirmed") == 0){ ?>
                        <p><?php echo $api->clean($row['service_type']) ?></p>
                      <?php } else { ?>
                        <select name="service_type" class="form-select form-select-md mb-3">
                          <?php if(strcasecmp($row['service_type'], 'Walk-in') == 0){ ?>
                            <option value="Walk-in" selected>Walk-in</option>
                            <option value="Home Service">Home Service</option>
                          <?php } else { ?>
                            <option value="Walk-in">Walk-in</option>
                            <option value="Home Service" selected>Home Service</option>
                          <?php } ?>
                        </select>
                      <?php } ?>
                    </div>
                    <!-- time -->
                    <div class="col">
                    <label for="scheduled_time" class="form-label fw-semibold">Scheduled Time</label>
                      <?php if(strcasecmp($row['reservation_status'], "Confirmed") == 0){ ?>
                        <p><?php echo $api->clean(date("g:i A", strtotime($row['scheduled_time']))); ?></p>
                      <?php } else { ?>
                        <input type="time" readonly class="<?php if(strcasecmp($row['reservation_status'], "Confirmed") == 0) { echo "reservations"; } ?> form-control-plaintext fw-bold" value="<?php echo $api->clean($row['scheduled_time']); ?>" name="scheduled_time" />
                      <?php } ?>
                    </div>
                    <!-- date -->
                    <div class="col">
                      <label for="scheduled_date" class="form-label fw-semibold">Scheduled Date</label>
                      <?php if(strcasecmp($row['reservation_status'], "Confirmed") == 0){ ?>
                        <p>
                          <?php echo $api->clean($date[0]) . " " . $api->clean($date[1]) . ", " . $api->clean($date[2]); ?>
                        </p>
                      <?php } else { ?>
                        <input type="date" readonly class="<?php if(strcasecmp($row['reservation_status'], "Confirmed") == 0) { echo "reservations"; } ?> form-control-plaintext fw-bold" value="<?php echo $row['scheduled_date'] ?>" name="scheduled_date" />
                      <?php } ?>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row my-5">
                <!-- address -->
                <div class="col">
                  <label for="reservation_address" class="form-label fw-semibold">Address</label>
                  <?php if(strcasecmp($row['reservation_status'], "Confirmed") == 0){ ?>
                    <p><?php echo $api->clean($row['reservation_address']) ?></p>
                  <?php } else { ?>
                    <input type="text" readonly class="<?php if(strcasecmp($row['reservation_status'], "Confirmed")) { echo "reservations"; } ?> form-control-plaintext fw-bold" value="<?php echo $api->clean($row['reservation_address']) ?>" name="reservation_address" />
                  <?php } ?>
                </div>
              </div>
              <div class="row <?php echo strcasecmp($row['reservation_status'], 'Confirmed') == 0 ? "mt-5" : "my-5"; ?>">
                <!-- demands -->
                <div class="col">
                  <label for="reservation_description" class="form-label fw-semibold">Demands</label>
                  <?php if(strcasecmp($row['reservation_status'], "Confirmed") == 0){ ?>
                    <p><?php echo $api->clean($row['reservation_description']) ?></p>
                  <?php } else { ?>
                    <input type="text" readonly class="<?php if(strcasecmp($row['reservation_status'], "Confirmed") == 0) { echo "reservations"; } ?> form-control-plaintext fw-bold" value="<?php echo $api->clean($row['reservation_description']) ?>" name="reservation_demands" />
                  <?php } ?>
                </div>
              </div>
              <?php if(strcasecmp($row['reservation_status'], 'Confirmed') != 0){ ?>
                <div class="row mt-1 mb-2">
                  <div class="col d-flex justify-content-end">
                    <button type="submit" class="order-0 btn btn-outline-primary d-none" name="update_reservation">Update</button>
                    <button type="submit" class="order-1 ms-1 btn btn-primary" name="confirm_reservation">Confirm Reservation</button>
                    <button type="submit" class="order-2 ms-1 btn btn-outline-danger" name="cancel_reservation">Cancel</button>
                  </div>
                </div>
              <?php } ?>
            </div>
          </form>
        </div>
      </div>
      <?php
        }
      } else {
      ?>
      <h1 class="my-5 display-6 fst-italic text-muted">No ongoing reservations.</h1>
      <?php
        }
      ?>
    </div>
  </div>  
</body>
<script src="../api/bootstrap-bundle-min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> -->
<script>
  var edit_reservations = document.getElementById('edit_reservations');
  var edit_reservations_label = document.getElementById('edit_reservations_label');
  var update_buttons = document.getElementsByName('update_reservation');
  var reservation_row_fields = document.querySelectorAll(".reservations.form-control-plaintext");

  edit_reservations.addEventListener('click', function() {
      this.checked ? edit_reservations_label.innerText = "Stop Editing" : edit_reservations_label.innerText = "Edit";
      
      for(var i=0, count=reservation_row_fields.length; i < count; i++){
        if(this.checked) {
          reservation_row_fields[i].readOnly = false;
          reservation_row_fields[i].className = "reservations form-control";
        } else {
          reservation_row_fields[i].readOnly = true;
          reservation_row_fields[i].className = "reservations form-control-plaintext";
        }
      }

      for(var j=0, count=update_buttons.length; j < count; j++){
        if(this.checked) {
          update_buttons[j].classList.remove('d-none');
          update_buttons[j].classList.add('d-inline');
        } else {
          update_buttons[j].classList.add('d-none');
          update_buttons[j].classList.remove('d-inline');
        }
      }
  });
</script>
</html>
<?php
  try {
    $api->free_result($statement);

    $mysqli_checks = $api->close($statement);
    if ($mysqli_checks===false) {
        throw new Exception('The prepared statement could not be closed.');
    }
  } catch (Exception $e) {
    echo $e->getMessage();
    Header("Location: ./index.php");
  }
?>