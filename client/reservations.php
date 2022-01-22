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
  }

  if(!isset($_SESSION['order_id']) || !empty($_SESSION['order_id'])){
    try {
      // get existing order
      $get_order = $api->select();
      $get_order = $api->params($get_order, "order_id");
      $get_order = $api->from($get_order);
      $get_order = $api->table($get_order, "workorder");
      $get_order = $api->where($get_order, array("client_id", "status"), array("?", "?"));
      $get_order = $api->limit($get_order, 1);
  
      $statement = $api->prepare($get_order);
      if ($statement===false) {
          throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
      }
  
      $mysqli_checks = $api->bind_params($statement, "ss", array($client_id, "Ongoing"));
      if ($mysqli_checks===false) {
          throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
      }
  
      $mysqli_checks = $api->execute($statement);
      if($mysqli_checks===false) {
          throw new Exception('Execute error: The prepared statement could not be executed.');
      }
  
      $api->store_result($statement);
      $_SESSION['order_id'] = "";
  
      if($api->num_rows($statement) > 0){
          $res = $api->bind_result($statement, array($_SESSION['order_id']));
          $api->get_bound_result($_SESSION['order_id'], $res[0]);
      } else {
          $_SESSION['order_id'] = "";
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
        Header("Location: ../client/explore.php");
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
  <link href="./style/bootstrap.css" rel="stylesheet">
  <link href="./style/style.css" rel="stylesheet">
  <title>Explore | NJC Tattoo</title>
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
  <div class="d-flex align-items-center">
    <form class="w-100" action="new_reservation.php" method="post">
      <?php
        try {
          $left = $api->join("", "tattoo", "order_item", "tattoo.tattoo_id", "order_item.tattoo_id");
          $join = $api->join("", $left, "workorder", "order_item.order_id", "workorder.order_id");

          // get all standing order items
          $get_order_items = $api->select();
          $get_order_items = $api->params($get_order_items, array("item_id", "tattoo_name", "tattoo_quantity"));
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
      <!-- Order Item --->
      <select name="item" id="item" class="form-select" required>
        <option value="" hidden selected>Order Item</option>
        <?php
          if($api->num_rows($res) > 0){
            while($row = $api->fetch_assoc($res)){
        ?>
        <option value="<?php echo $row['item_id'] ?>"><?php echo $row['tattoo_quantity'] . " pc. ". $row['tattoo_name'] ?></option>
        <?php
            }
        ?>
      </select>
      <button type="submit" class="resbtn" name="submit"><span class="material-icons md-48 lh-base">add</span>New Reservation</button>
        <?php       
          } else {
        ?>
        <option value="" selected>No items available for resevation.</option>
        <?php
          }
        ?>
      </select>
    </form>    
  <!-- Reservation Details Main Div --> 
  <div class="details">
    <div class="details_header">
      <p>Ongoing Reservations</p>
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="edit_reservations" />
        <label class="form-check-label" for="edit_reservations" id="edit_reservations_label">Edit</label>
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

          $left = $api->join("", "reservation", "workorder", "reservation.workorder_id", "workorder.order_id");
          $right = $api->join("", $left, "order_item", "reservation.item_id", "order_item.item_id");
          $join = $api->join("", $right, "tattoo", "tattoo.tattoo_id", "order_item.tattoo_id");

          $query = $api->select();
          $query = $api->params($query, array("reservation_id", "reservation_status", "reservation.item_id", "predecessor_id", "tattoo_name", "order_item.tattoo_quantity", "order_item.tattoo_width", "order_item.tattoo_height", "service_type", "scheduled_date", "scheduled_time", "reservation_address", "reservation_description"));
          $query = $api->from($query);
          $query = $api->table($query, $join);
          $query = $api->where($query, array("reservation.workorder_id", "order_item.item_status"), array("?", "?"));

          $in = "AND reservation_status IN (?, ?) ";
          $query = $query . $in;
          $query = $api->order($query, "scheduled_date", "ASC");

          try {
            $statement = $api->prepare($query);
            if ($statement===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }
        
            $mysqli_checks = $api->bind_params($statement, "ssss", array($order_id, "Reserved", "Pending", "Confirmed"));
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

        if($api->num_rows($res) > 0){
          while($row = $api->fetch_assoc($res)){
        ?>
          <button type="button"><h5><?php echo $row['scheduled_date'] ?></h5></button>
          <div class="dateDiv">

            <!--- Reservation Details -->
            <div class="res_info">
                <div>
                    <img src="img/pic1.jpg">
                </div>
                <form action="../api/queries.php" method="POST" class="form px-3">
                  <div class="mt-3">
                    <div class="row">
                      <!--- Status--->
                      <div class="col-md-3 mt-4">
                        <label for="Status" class="form-label">Status</label>
                        <p><?php echo $row['reservation_status'] ?></p>
                        <input type="hidden" readonly class="d-none" value="<?php echo $row['reservation_id'] ?>" name="reservation_id" />
                      </div>
                      <!--- Tattoo Name--->
                      <div class="col-md-3 mt-4">
                        <label class="form-label">Reserved Item</label>
                        <p><?php echo $row['tattoo_name'] ?></p>
                        <input type="hidden" readonly class="d-none" value="<?php echo $row['item_id'] ?>" name="item_id" />
                        <input type="hidden" readonly class="d-none" value="<?php echo $row['predecessor_id'] ?>" name="predecessor_id" />
                      </div>
                      <!--- Item Quantity--->
                      <div class="col-md-3 mt-4">
                        <label for="quantity" class="form-label">Quantity</label>
                        <p><?php echo $row['tattoo_quantity'] . " pc. "?></p>
                        <input type="hidden" readonly class="d-none" value="<?php echo $row['tattoo_quantity'] ?>" name="quantity" />
                      </div>
                      <!--- Width--->
                      <div class="col-md-3 mt-4">
                        <label for="inputAddress" class="form-label">Width</label>
                        <p><?php echo $row['tattoo_width'] . " in." ?></p>
                      </div>
                    </div>
                    <div class="row">
                      <!--- Height--->
                      <div class="col-md-3 mt-4">
                        <label for="inputAddress" class="form-label">Height</label>
                        <p><?php echo $row['tattoo_height'] . " in." ?></p>
                      </div>
                      <!--- Service Type--->
                      <div class="col-md-3 mt-4">
                        <label for="inputAddress" class="form-label">Service Type</label>
                        <select name="service_type" class="form-select form-select-md mb-3">
                          <?php if(strcasecmp($row['service_type'], 'Walk-in') == 0){ ?>
                            <option value="Walk-in" selected>Walk-in</option>
                            <option value="Home Service">Home Service</option>
                          <?php } else { ?>
                            <option value="Walk-in">Walk-in</option>
                            <option value="Home Service" selected>Home Service</option>
                          <?php } ?>
                        </select>
                      </div>
                      <!--- Time --->
                      <div class="col-md-3 mt-4">
                        <label for="inputAddress" class="form-label">Scheduled Time</label>
                        <input type="time" readonly class="<?php if(strcasecmp($row['reservation_status'], "Confirmed")) { echo "reservations"; } ?> form-control-plaintext fw-bold" value="<?php echo $row['scheduled_time']; ?>" name="scheduled_time" />
                      </div>
                      <!--- Date --->
                      <div class="col-md-3 mt-4">
                        <label for="inputAddress" class="form-label">Scheduled Date</label>
                        <input type="date" readonly class="<?php if(strcasecmp($row['reservation_status'], "Confirmed")) { echo "reservations"; } ?> form-control-plaintext fw-bold" value="<?php echo $row['scheduled_date'] ?>" name="scheduled_date" />
                      </div>
                    </div>
                    <div class="row">
                      <!--- Address --->
                      <div class="col-md-12 mt-4">
                        <label for="inputAddress" class="form-label">Address</label>
                        <input type="text" readonly class="<?php if(strcasecmp($row['reservation_status'], "Confirmed")) { echo "reservations"; } ?> form-control-plaintext fw-bold" value="<?php echo $row['reservation_address'] ?>" name="reservation_address" />
                      </div>
                    </div>
                    <div class="row">
                      <!--- Demands --->
                      <div class="col-md-12 mt-4">
                          <label for="inputAddress" class="form-label">Demands</label>
                          <input type="text" readonly class="<?php if(strcasecmp($row['reservation_status'], "Confirmed")) { echo "reservations"; } ?> form-control-plaintext fw-bold" value="<?php echo $row['reservation_description'] ?>" name="reservation_demands" />
                      </div>
                    </div>
                    <?php if(strcasecmp($row['reservation_status'], 'Confirmed') != 0){ ?>
                      <button type="submit" class="btn btn-outline-info d-none" name="update_reservation">Update</button>
                      <button type="submit" class="btn btn-outline-primary" name="confirm_reservation">Confirm</button>
                      <button type="submit" class="btn btn-outline-danger" name="cancel_reservation">Cancel</button>
                    <?php } ?>
                  </div>
                </form>
            </div>
        </div>
    <?php
      }}   
    ?>
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

      for(var i=0, count=update_buttons.length; i < count; i++){
        if(this.checked) {
          update_buttons[i].classList.remove('d-none');
          update_buttons[i].classList.add('d-inline');
        } else {
          update_buttons[i].classList.add('d-none');
          update_buttons[i].classList.remove('d-inline');
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