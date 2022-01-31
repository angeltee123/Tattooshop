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
  <div class="w-80 mx-auto">
    <h2 class="col fw-bold display-6">Ongoing Sessions</h2>
    <div class="my-5 vstack">
      <?php
        try {
          $workorder = $api->join("INNER", "workorder", "order_item", "workorder.order_id", "order_item.order_id");
          $client = $api->join("INNER", $workorder, "client", "workorder.client_id", "client.client_id");
          $reservation = $api->join("INNER", $client, "reservation", "reservation.item_id", "order_item.item_id");
          $worksession = $api->join("INNER", $reservation, "worksession", "reservation.reservation_id", "worksession.reservation_id");
          $join = $api->join("INNER", $worksession, "tattoo", "tattoo.tattoo_id", "order_item.tattoo_id");

          $query = $api->select();
          $query = $api->params($query, array("client_fname", "client_mi", "client_lname", "worksession.reservation_id", "session_id", "session_status", "reservation.item_id", "tattoo_name", "tattoo_image", "tattoo_quantity", "order_item.tattoo_width", "order_item.tattoo_height", "service_type", "amount_addon", "session_date", "session_start_time", "session_address", "reservation_description"));
          $query = $api->from($query);
          $query = $api->table($query, $join);
          $query = $query . "WHERE session_status!= ? ";
          $query = $api->order($query, "session_date", "ASC");

          $statement = $api->prepare($query);
          if ($statement===false) {
              throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
          }

          $mysqli_checks = $api->bind_params($statement, "s", "Finished");
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
            Header("Location: ./index.php");
        }

        if($api->num_rows($res) > 0){
          while($row = $api->fetch_assoc($res)){
      ?>
      <div class="my-4 shadow-sm">
        <form action="./queries.php" method="POST">
          <div class="collapsible d-flex align-items-center justify-content-between">
            <button type="button" class="col bg-none text-start p-4 border-0" data-bs-toggle="collapse" data-bs-target="#item_<?php echo $api->clean($row['item_id']) ?>" aria-expanded="false" aria-controls="item_<?php echo $api->clean($row['item_id']) ?>">
            <h5 class="d-inline">
              <?php
                $date = date("M:d:Y", strtotime($api->clean($row['session_date'])));
                $date = explode(':', $date);
                echo $api->clean($row['client_fname']) . " " . $api->clean($row['client_mi']) . ". " . $api->clean($row['client_lname']) . " " . $api->clean($row['tattoo_quantity']) . " pc. " . $api->clean($row['tattoo_name']);
              ?>
            </h5><p class="d-inline text-muted"><?php echo " on " . $api->clean($date[0]) . " " . $api->clean($date[1]) . ", " . $api->clean($date[2]) ?></p></button>
            <div class="w-auto mx-3">
              <button type="submit" class="order-1 btn btn-primary" name="finish_worksession">Finish Worksession</button>
            </div>
          </div>
          <div class="collapse border-top-0 p-5" id="item_<?php echo $api->clean($row['item_id']) ?>">
              <div class="mt-3">
                <div class="d-flex align-items-center justify-content-between">
                  <!-- tattoo image -->
                  <div class="tattoo-image shadow-sm border-2 rounded" style="background-image: url(<?php echo $api->clean($row['tattoo_image']); ?>)"></div>
                  <div class="w-100 ms-6">
                    <div class="row my-5">
                      <!-- tattoo name -->
                      <div class="col">
                        <label class="form-label fw-semibold">Applied Item</label>
                        <p><?php echo $api->clean($row['tattoo_name']) ?></p>
                        <input type="hidden" readonly class="d-none" value="<?php echo $api->clean($row['item_id']) ?>" name="item_id" />
                      </div>
                      <!-- status -->
                      <div class="col">
                        <label for="status" class="form-label fw-semibold">Status</label>
                        <p><?php echo $api->clean($row['session_status']) ?></p>
                        <input type="hidden" readonly class="d-none" value="<?php echo $api->clean($row['session_id']) ?>" name="session_id" />
                        <input type="hidden" readonly class="d-none" value="<?php echo $api->clean($row['reservation_id']) ?>" name="reservation_id" />
                      </div>
                      <!-- addon amount -->
                      <div class="col">
                        <label class="form-label fw-semibold">Add-on Amount</label>
                        <p><?php echo $api->clean($row['amount_addon']) ?></p>
                      </div>
                    </div>
                    <div class="row my-5">
                      <!-- quantity -->
                      <div class="col">
                        <label for="quantity" class="form-label fw-semibold">Quantity</label>
                        <p><?php echo $api->clean($row['tattoo_quantity']) . " pc. "?></p>
                      </div>
                      <!-- width -->
                      <div class="col">
                        <label for="width" class="form-label fw-semibold">Width</label>
                        <p><?php echo $api->clean($row['tattoo_width']) . " in." ?></p>
                      </div>
                      <!-- height --->
                      <div class="col">
                        <label for="inputAddress" class="form-label fw-semibold">Height</label>
                        <p><?php echo $api->clean($row['tattoo_height']) . " in." ?></p>
                      </div>
                    </div>
                    <div class="row my-5">
                      <!-- service type -->
                      <div class="col">
                        <label for="service_type" class="form-label fw-semibold">Service Type</label>
                        <p><?php echo $api->clean($row['service_type']) ?></p>
                      </div>
                      <!-- time -->
                      <div class="col">
                        <label for="scheduled_time" class="form-label fw-semibold">Start Time</label>
                        <p><?php echo date("g:i A", strtotime($row['session_start_time'])); ?></p>
                      </div>
                      <!-- date -->
                      <div class="col">
                        <label for="scheduled_date" class="form-label fw-semibold">Session Date</label>
                        <p><?php echo $api->clean($date[0]) . " " . $api->clean($date[1]) . ", " . $api->clean($date[2]) ?></p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row my-5">
                  <!-- address -->
                  <div class="col">
                    <label for="reservation_address" class="form-label fw-semibold">Address</label>
                    <p><?php echo $api->clean($row['session_address']) ?></p>
                  </div>
                </div>
                <div class="row mt-5">
                  <!-- demands -->
                  <div class="col">
                    <label for="reservation_description" class="form-label fw-semibold">Demands</label>
                    <p><?php echo $api->clean($row['reservation_description']) ?></p>
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
      <h1 class="my-5 display-6 fst-italic text-muted">No sessions ongoing.</h1>
      <?php
        }
      ?>
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
    </div>
    <h2 class="col fw-bold display-6">Ongoing Reservations</h2>
    <div class="my-5 vstack">
      <?php
        try {
          $statement = $api->prepare("SELECT workorder.client_id, client_fname, client_mi, client_lname, reservation_id, reservation_status, reservation.item_id, tattoo_name, tattoo_image, tattoo_quantity, order_item.tattoo_width, order_item.tattoo_height, service_type, amount_addon, scheduled_date, scheduled_time, reservation_address, reservation_description FROM ((((workorder INNER JOIN order_item ON workorder.order_id=order_item.order_id) INNER JOIN client ON workorder.client_id=client.client_id) INNER JOIN reservation ON reservation.item_id=order_item.item_id) INNER JOIN tattoo ON tattoo.tattoo_id=order_item.tattoo_id) WHERE reservation_id NOT IN (SELECT reservation_id FROM worksession) AND item_status=? AND reservation_status IN (?, ?) ORDER BY scheduled_date ASC");
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
            Header("Location: ./index.php");
        }

        if($api->num_rows($res) > 0){
          while($row = $api->fetch_assoc($res)){
      ?>
      <div class="my-4 shadow-sm">
        <form action="./queries.php" method="POST">
          <div class="collapsible d-flex align-items-center justify-content-between">
            <button type="button" class="col bg-none text-start p-4 border-0" data-bs-toggle="collapse" data-bs-target="#item_<?php echo $api->clean($row['item_id']) ?>" aria-expanded="false" aria-controls="item_<?php echo $api->clean($row['item_id']) ?>">
            <h5 class="d-inline">
              <?php
                $date = date("M:d:Y", strtotime($api->clean($row['scheduled_date'])));
                $date = explode(':', $date);
                echo $api->clean($row['client_fname']) . " " . $api->clean($row['client_mi']) . ". " . $api->clean($row['client_lname']) . " " . $api->clean($row['tattoo_quantity']) . " pc. " . $api->clean($row['tattoo_name']);
              ?>
            </h5><p class="d-inline text-muted"><?php echo " on " . $api->clean($date[0]) . " " . $api->clean($date[1]) . ", " . $api->clean($date[2]) ?></p></button>
            <div class="w-auto mx-3">
              <button type="submit" class="order-0 btn btn-outline-primary" name="update_reservation">Update</button>
              <?php if(strcasecmp($row['reservation_status'], "Confirmed") == 0) {?>
                <button type="submit" class="order-1 btn btn-primary" name="start_worksession">Start Worksession</button>
              <?php } ?>
              <input type="hidden" readonly class="d-none" value="<?php echo $api->clean($row['client_id']) ?>" name="client_id" />
              <button type="submit" class="order-2 btn btn-outline-danger" name="cancel_reservation">Cancel Reservation</button>
            </div>
          </div>
          <div class="collapse border-top-0 p-5" id="item_<?php echo $api->clean($row['item_id']) ?>">
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
                      <p class="fw-semibold <?php if(strcasecmp($row['reservation_status'], "Confirmed") == 0){ echo "text-success"; } else { echo "text-secondary"; } ?>"><?php echo $api->clean($row['reservation_status']) ?></p>
                      <input type="hidden" readonly class="d-none" value="<?php echo $api->clean($row['reservation_id']) ?>" name="reservation_id" />
                    </div>
                    <!-- addon amount -->
                    <div class="col">
                      <label class="form-label fw-semibold">Add-on Amount</label>
                      <input type="number" class="form-control" value="<?php echo $api->clean($row['amount_addon']) ?>" name="amount_addon" />
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
                      <label for="inputAddress" class="form-label fw-semibold">Height</label>
                      <p><?php echo $api->clean($row['tattoo_height']) . " in." ?></p>
                    </div>
                  </div>
                  <div class="row my-5">
                    <!-- service type -->
                    <div class="col">
                      <label for="service_type" class="form-label fw-semibold">Service Type</label>
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
                    <!-- time -->
                    <div class="col">
                      <label for="scheduled_time" class="form-label fw-semibold">Scheduled Time</label>
                      <input type="time" class="form-control fw-bold" value="<?php echo $api->clean($row['scheduled_time']); ?>" name="scheduled_time" />
                    </div>
                    <!-- date -->
                    <div class="col">
                      <label for="scheduled_date" class="form-label fw-semibold">Scheduled Date</label>
                      <input type="date" class="form-control fw-bold" value="<?php echo $row['scheduled_date'] ?>" name="scheduled_date" />
                    </div>
                  </div>
                </div>
              </div>
              <div class="row my-5">
                <!-- address -->
                <div class="col">
                  <label for="reservation_address" class="form-label fw-semibold">Address</label>
                  <input type="text" class="form-control fw-bold" value="<?php echo $api->clean($row['reservation_address']) ?>" name="reservation_address" />
                </div>
              </div>
              <div class="row mt-5">
                <!-- demands -->
                <div class="col">
                  <label for="reservation_description" class="form-label fw-semibold">Demands</label>
                  <p><?php if(!empty($row['reservation_description'])) { echo $api->clean($row['reservation_description']); } else { echo "None"; } ?></p>
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
      <h1 class="my-5 display-6 fst-italic text-muted">No ongoing reservations.</h1>
      <?php
        }
      ?>
    </div>
  </div>  
</body>
<script src="../api/bootstrap-bundle-min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> -->
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