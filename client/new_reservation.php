<?php
  session_name("sess_id");
  session_start();
  if(!isset($_SESSION['user']['user_id']) || strcasecmp($_SESSION['user']['user_type'], "User") != 0){
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
    $client_id = $api->sanitize_data($_SESSION['user']['client_id'], "string");
    $item_id = $api->sanitize_data($_POST['item'], "string");
  }

  try {
    $statement = $api->prepare("SELECT tattoo_image, tattoo_quantity FROM (tattoo JOIN order_item ON tattoo.tattoo_id=order_item.tattoo_id) WHERE item_id=?");
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
    $_SESSION['res'] = $e->getMessage();
    Header("Location: ./reservations.php");
    exit();
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require_once '../common/meta.php'; ?>
  <!-- native style -->
  <style>
    .New-booking {
      justify-content: center;
      align-content: flex-start;
      width: 100%;
      height: 100vh;
      margin-left: 0;
      margin-right: 0;
    }

    .New-booking > div {
      height: 100%;
    }

    .New-booking__preview {
      order: -1 !important;
      background-position: center;
      background-repeat: no-repeat;
      background-size: cover;
    }

    .New-booking__preview__back {
      display: flex;
      position: absolute;
      justify-content: center;
      align-items: center;
      background-color: #fff !important;
      top: 0;
      left: 0;
      margin-top: 3rem;
      margin-left: 3rem;
      width: 75px;
      height: 75px;
    }

    .New-booking__form {
      order: 6 !important;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    #book {
      display: flex;
      border-radius: 50rem !important;
      align-items: center;
    }
  </style>
  <title>New Reservation | NJC Tattoo</title>
</head>
<body class="w-100 h-100">
  <div class="New-booking row">
    <div class="New-booking__preview col-5" style="background-image: url(<?php echo $tattoo_image; ?>);">
      <div class="New-booking__preview__back border">
        <a href="./reservations.php" class="stretched-link"><span class="material-icons md-48 display-5" style="width: 24px;">arrow_back_ios</span></a>
      </div>
    </div>  
    <div class="New-booking__form col">
      <div class="flex-grow-1">
        <div class="ms-6 w-60">
          <h1 class="display-4 fw-bold">New Booking</h1>
          <form class="mt-4" action="../scripts/php/queries.php" method="post">
            <input type="hidden" readonly class="d-none" name="item_id" value="<?php echo $item_id; ?>" required/>
            <input type="hidden" readonly class="d-none" name="original_quantity" id="original_quantity" value="<?php echo $quantity; ?>" required/>
            <div class="row mb-4">
              <div class="col-5">
                <div class="form-floating">
                  <input type="number" class="form-control" name="quantity" id="quantity" min="1" max="<?php echo $quantity; ?>" value="<?php echo $quantity; ?>" placeholder="Quantity" required/>
                  <label for="quantity">Quantity</label>
                  <p class="my-2 <?php echo isset($_SESSION['quantity_err']) ? "d-block" : "d-none"; ?> text-danger"><?php if(isset($_SESSION['quantity_err'])){ echo $_SESSION['quantity_err']; } ?></p>
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
              <input type="text" readonly class="form-control" name="address" id="address" value="Mandaue City, Cebu" placeholder="Service Location" required/>
              <label for="address">Service Location</label>
              <p class="my-2 <?php echo isset($_SESSION['address_err']) ? "d-block" : "d-none"; ?> text-danger"><?php if(isset($_SESSION['address_err'])){ echo $_SESSION['address_err']; } ?></p>
            </div>
            <div class="form-floating mb-4">
              <input type="date" class="form-control" name="scheduled_date" id="scheduled_date" placeholder="Date" required/>
              <label for="scheduled_date">Date</label>
              <p class="my-2 <?php echo isset($_SESSION['scheduled_date_err']) ? "d-block" : "d-none"; ?> text-danger"><?php if(isset($_SESSION['scheduled_date_err'])){ echo $_SESSION['scheduled_date_err']; } ?></p>
            </div>
            <div class="form-floating mb-4">
              <input type="time" class="form-control" name="scheduled_time" id="scheduled_time" placeholder="Time" required/>
              <label for="scheduled_time">Time</label>
              <p class="my-2 <?php echo isset($_SESSION['scheduled_time_err']) ? "d-block" : "d-none"; ?> text-danger"><?php if(isset($_SESSION['scheduled_time_err'])){ echo $_SESSION['scheduled_time_err']; } ?></p>
            </div>
            <div class="mb-4">
              <textarea class="form-control p-3 text-wrap" name="description" rows="5" placeholder="Demands (Optional)"></textarea>
              <p class="my-2 d-none text-danger"></p>
            </div>
            <button type="submit" class="btn btn-dark" name="book" id="book"><span class="material-icons lh-base pe-2">bookmark_add</span>Book Now</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script>
  var submit = true;

  // input fields
  var quantity = document.getElementById('quantity');
  var service_type = document.getElementById('service_type');
  var address = document.getElementById('address');
  var scheduled_date = document.getElementById('scheduled_date');
  var scheduled_time = document.getElementById('scheduled_time');

  service_type.addEventListener('change', function(){
    if(service_type.value.localeCompare("Walk-in") == 0){
      address.readOnly = true;
      address.value = "Mandaue City, Cebu";
    } else {
      address.readOnly = false;
    }
  });
</script>
</html>