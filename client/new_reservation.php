<?php
  session_name("sess_id");
  session_start();

  // navigation guard
  if(!isset($_SESSION['user']['user_id']) || strcasecmp($_SESSION['user']['user_type'], "User") != 0){
    Header("Location: ../admin/index.php");
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

  // retrieve item data
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
  <!-- meta -->
  <?php require_once '../common/meta.php'; ?>
  
  <!-- external stylesheets -->
  <link href="../style/catalogue.css" rel="stylesheet" scoped>
  <link href="./style/explore.css" rel="stylesheet" scoped>
  
  <!-- native style -->
  <style scoped>
    .Catalogue__cards__modal {
      display: flex !important;
    }

    #book {
      display: flex;
      border-radius: 50rem !important;
      align-items: center;
    }

    /* xxl breakpoint */
    @media (max-width: 1400px){
      .Catalogue__cards__modal__preview-body__form {
        margin: 0 auto !important;
        width: 100% !important;
      }
    }
  </style>
  <title>New Reservation | NJC Tattoo</title>
</head>
<body>
  <!-- new booking -->
  <div class="Catalogue__cards__modal overflow-auto">

    <!-- item preview -->
    <div class="Catalogue__cards__modal__preview" style="width: 39.5%;">
      <div class="Catalogue__cards__modal__preview__image" style="background-image: url(<?php echo $tattoo_image; ?>);"></div>
    </div>

    <!-- tattoo body -->
    <div class="Catalogue__cards__modal__preview-body flex-grow-1">
      <!-- close modal -->
      <div class="Catalogue__cards__modal--back">
        <a href="./reservations.php" class="stretched-link"><span class="material-icons md-48 display-5" style="width: 24px;">arrow_back_ios</span></a>
      </div>

      <!-- modal form -->
      <div class="Catalogue__cards__modal__preview-body__form">
        <form id="New-booking__form" class="Catalogue__cards__modal__form" action="../scripts/php/queries.php" method="post">
          <h1 class="display-4 fw-bold mb-3" style="font-family: 'Yeseva One', cursive, serif;">New Booking</h1>
          <input type="hidden" readonly class="d-none" name="item_id" value="<?php echo $item_id; ?>" required/>
          <input type="hidden" readonly class="d-none" name="original_quantity" id="original_quantity" value="<?php echo $quantity; ?>" required/>
          
          <div class="row mb-3">
            <!-- item quantity -->
            <div class="col-12 col-lg-5 my-2">
              <div class="form-floating">
                <input type="number" class="form-control" name="quantity" id="quantity" min="1" max="<?php echo $quantity; ?>" value="<?php echo $quantity; ?>" placeholder="Quantity" required/>
                <label for="quantity">Quantity</label>
              </div>
              <label id="quantity_err" class="error-message <?php echo isset($_SESSION['quantity_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['quantity_err'])) { echo $_SESSION['quantity_err']; } ?></span></label>
            </div>

            <!-- service type -->
            <div class="col-12 col-lg my-2">
              <div class="form-floating">
                <select name="service_type" id="service_type" class="form-select">
                  <option value="Walk-in" selected>Walk-in</option>
                  <option value="Home Service">Home Service</option>
                </select>
                <label for="service_type">Service Type</label>
              </div>
            </div>
          </div>

          <!-- service location -->
          <div class="mb-4">
            <div class="form-floating">
              <input type="text" readonly class="form-control" name="address" id="address" value="Mandaue City, Cebu" placeholder="Service Location" required/>
              <label for="address">Service Location</label>
            </div>
            <label id="address_err" class="error-message <?php echo isset($_SESSION['address_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['address_err'])) { echo $_SESSION['address_err']; } ?></span></label>
          </div>

          <!-- date -->
          <div class="mb-4">
            <div class="form-floating">
              <input type="date" class="form-control" name="scheduled_date" id="scheduled_date" placeholder="Date" required/>
              <label for="scheduled_date">Date</label>
            </div>
            <label id="scheduled_date_err" class="error-message <?php echo isset($_SESSION['scheduled_date_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['scheduled_date_err'])) { echo $_SESSION['scheduled_date_err']; } ?></span></label>
          </div>

          <!-- time -->
          <div class="mb-4">
            <div class="form-floating">
              <input type="time" class="form-control" name="scheduled_time" id="scheduled_time" placeholder="Time" required/>
              <label for="scheduled_time">Time</label>
            </div>
            <label id="scheduled_time_err" class="error-message <?php echo isset($_SESSION['scheduled_time_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['scheduled_time_err'])) { echo $_SESSION['scheduled_time_err']; } ?></span></label>
          </div>

          <!-- reservation demands -->
          <div class="mb-4">
            <textarea class="form-control p-3 text-wrap" name="description" rows="5" placeholder="Demands (Optional)"></textarea>
          </div>

          <!-- book reservation -->
          <button type="submit" class="btn btn-dark" name="book" id="book"><span class="material-icons lh-base pe-2">bookmark_add</span>Book Now</button>
        </form>
      </div>
    </div>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script src="../api/api.js"></script>
<script src="../scripts/js/new_reservation.js"></script>
</html>