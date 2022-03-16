<?php
  session_name("sess_id");
  session_start();
  if(!isset($_SESSION['user_id'])){
    Header("Location: ./reservations.php");
    die();
  } elseif(!isset($_POST) || empty($_POST)){
    $warning = "Please select an item.";
    echo "<script>alert('$warning');</script>";
    Header("Location: ./reservations.php");
    die();
  } else {
    require_once '../api/api.php';
    $api = new api();
    $client_id = $api->sanitize_data($_SESSION['client_id'], "string");
    $item_id = $api->sanitize_data($_POST['item'], "string");
  }

  $join = $api->join("", "tattoo", "order_item", "tattoo.tattoo_id", "order_item.tattoo_id");
  $query = $api->select();
  $query = $api->params($query, array("tattoo_image", "tattoo_quantity"));
  $query = $api->from($query);
  $query = $api->table($query, $join);
  $query = $api->where($query, "item_id", "?");

  try {
    $statement = $api->prepare($query);
    if($statement===false){
        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
    }

    $mysqli_checks = $api->bind_params($statement, "s", $item_id);
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

    if($api->num_rows($res) > 0){
      $item = $api->fetch_assoc($res);
      $tattoo_image = $api->sanitize_data($item['tattoo_image'], "string");
      $quantity = $api->sanitize_data($item['tattoo_quantity'], "int");
    } else {
      throw new Exception('The prepared statement could not be closed.');
    }

    $api->free_result($statement);
    $mysqli_checks = $api->close($statement);
    if($mysqli_checks===false){
      throw new Exception('Error: Retrieving order item data failed.');
    } 
  } catch(Exception $e) {
    exit();
    $_SESSION['res'] = $e->getMessage();
    Header("Location: ./reservations.php");
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require_once '../common/meta.php'; ?>
  <!-- native style -->
  <style>
    .content .row > div {
      height: 100%;
    }

    #preview {
      background-position: center;
      background-repeat: no-repeat;
      background-size: cover;
    }
  </style>
  <title>New Reservation | NJC Tattoo</title>
</head>
<body>
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
                <form action="../scripts/php/queries.php" method="post">
                  <button type="submit" class="dropdown-item btn-link" name="logout">Sign Out</button>
                </form>
              </li>
            </ul>
        </div>
      </div>
    </nav>
  </header>
  <div class="content w-80">
    <div class="row justify-content-center align-items-start w-100 vh-100 mx-0">
      <div class="col-5 order-first border rounded shadow" id="preview" style="background-image: url(<?php echo $tattoo_image; ?>);"></div>  
      <div class="col order-last d-flex justify-content-center align-items-center">
        <div class="flex-grow-1">
          <div class="ms-4 me-8">
            <form class="mt-4" action="../scripts/php/queries.php" method="post">
              <input type="hidden" readonly class="d-none" value="<?php echo $item_id; ?>" name="item_id" />
              <input type="hidden" readonly class="d-none" name="original_quantity" value="<?php echo $quantity; ?>" required />
              <div class="row mb-4">
                <div class="col-5">
                  <div class="form-floating">
                    <input type="number" class="form-control" placeholder="Quantity" min="1" max="<?php echo $quantity; ?>" value="<?php echo $quantity; ?>" id="quantity" name="quantity" required />
                    <label for="quantity">Quantity</label>
                    <p class="my-2 <?php echo isset($_SESSION['width_err']) ? "d-block" : "d-none"; ?> text-danger width_err"><?php if(isset($_SESSION['width_err'])){ echo $_SESSION['width_err']; } ?></p>
                  </div>
                </div>
                <div class="col">
                  <div class="form-floating">
                    <select name="service_type" id="service_type" class="form-select">
                      <option value="Walk-in" selected>Walk-in</option>
                      <option value="Home Service">Home Service</option>
                    </select>
                    <label for="service_type">Service Type</label>
                  </div>
                </div>
              </div>
              <div class="form-floating mb-4">
                <input type="text" disabled class="form-control" placeholder="Service Location" name="address" id="address" value="Mandaue City, Cebu" required />
                <label for="address">Service Location</label>
                <p class="my-2 <?php echo isset($_SESSION['width_err']) ? "d-block" : "d-none"; ?> text-danger width_err"><?php if(isset($_SESSION['width_err'])){ echo $_SESSION['width_err']; } ?></p>
              </div>
              <div class="form-floating mb-4">
                <input type="date" class="form-control" placeholder="Date" name="scheduled_date" id="scheduled_date" required />
                <label for="scheduled_date">Date</label>
                <p class="my-2 <?php echo isset($_SESSION['width_err']) ? "d-block" : "d-none"; ?> text-danger width_err"><?php if(isset($_SESSION['width_err'])){ echo $_SESSION['width_err']; } ?></p>
              </div>
              <div class="form-floating mb-4">
                <input type="time" class="form-control" placeholder="Time" name="scheduled_time" id="scheduled_time" required />
                <label for="scheduled_time">Time</label>
                <p class="my-2 <?php echo isset($_SESSION['width_err']) ? "d-block" : "d-none"; ?> text-danger width_err"><?php if(isset($_SESSION['width_err'])){ echo $_SESSION['width_err']; } ?></p>
              </div>
              <div class="mb-4">
                <textarea class="form-control p-3 text-wrap" name="reservation_demands" rows="5" placeholder="Demands (Optional)"></textarea>
                <p class="my-2 d-none text-danger"></p>
              </div>
              <button type="submit" class="btn btn-dark rounded-pill d-flex align-items-center" name="book"><span class="material-icons lh-base pe-2">bookmark_add</span>Book Now</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</html>