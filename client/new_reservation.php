<?php
  session_name("sess_id");
  session_start();
  if(!isset($_SESSION['user_id'])){
    Header("Location: ./reservations.php");
    die();
  } elseif(empty($_POST)) {
    $warning = "Please select an item";
    echo "<script>alert('$warning');</script>";
    die();
  } else {
    require_once '../api/api.php';
    $api = new api();
    $client_id = $_SESSION['client_id'];
    $id = $_POST['item'];
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
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
  <title>New Booking | NJC Tattoo</title>
  <style>
  main{
      display:flex;
      flex-direction:column;
      width: 100vw;
      height:100vh;
      text-align:center;
      background-image: url("img/reserve.png");
      background-position: center;
      background-repeat: no-repeat;
      background-size: cover;
      padding-top:70px;
  }
  
  .tatname {
      border-radius: 3px;
      margin-left: 12px;
      width: 255px;
  } 
  
  </style>
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
                <form action="../api/queries.php" method="post">
                  <button type="submit" class="dropdown-item btn-link" name="logout">Sign Out</button>
                </form>
              </li>
            </ul>
        </div>
      </div>
    </nav>
  </header>
  <main>
    <div class="container">
      <?php
        $join = $api->join("", "tattoo", "order_item", "tattoo.tattoo_id", "order_item.tattoo_id");
        $query = $api->select();
        $query = $api->params($query, "*");
        $query = $api->from($query);
        $query = $api->table($query, $join);
        $query = $api->where($query, "item_id", "?");

        try{
          $statement = $api->prepare($query);
          if ($statement===false) {
              throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
          }
      
          $mysqli_checks = $api->bind_params($statement, "s", $id);
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
            Header("Location: ../client/reservations.php");
        }

        if($api->num_rows($res) <= 0){
          Header("Location: ../client/reservations.php");
        } else {
          $row = $api->fetch_assoc($res);
      ?>
      <form action="../api/queries.php" method="POST" class="form px-3">
        <div class="mt-3">
            <div class="row">
                <h2><?php echo $row['tattoo_name'] ?></h2>
                <div class="col-md-3 mt-4 btn-light tatname">
                  <input type="hidden" readonly class="d-none" value="<?php echo $api->sanitize_data($row['item_id'], 'string'); ?>" name="item_id" />
                </div>

                <!--- Item Quantity--->
                <div class="col-md-3 mt-4">
                  <input type="hidden" class="d-none" name="original_quantity" value="<?php echo $api->sanitize_data($row['tattoo_quantity'], 'int'); ?>" required />
                  <input type="text" class="form-control" name="quantity" id="quantity" value="<?php echo $api->sanitize_data($row['tattoo_quantity'], 'int'); ?>" min="1" max="<?php echo $api->sanitize_data($row['tattoo_quantity'], 'int') ?>" placeholder="Item Quantity" required />
                </div>
            </div>

            <div class="row">

                <!--- Service Type--->
                <div class="col-md-4 mt-4">
                    <select name="service_type" class="form-select form-select-md mb-3" >
                        <option value="" hidden selected> Service Type</option>
                        <option value="Walk-in">Walk-in</option>
                        <option value="Home Service">Home Service</option>
                    </select>
                </div>

                <!--- Time --->
                <div class="col-md-4 mt-4">
                    <input type="time" class="form-control" id="time" name="scheduled_time">
                </div>
            
                <!--- Date --->
                <div class="col-md-4 mt-4">
                    <input type="date" class="form-control" id="date" name="scheduled_date" required>
                </div>
            </div>
            
            <div class="row">
                <!--- Address --->
                <div class="col-md-12 mt-4">
                    <input type="text" class="form-control" name="address" placeholder="Address" required>
                </div>
            </div>

            <div class="row">
                <!--- Demands --->
                <div class="col-md-12 mt-4">
                    <input type="text" class="form-control" name="description" placeholder="Demands" required>
                </div>
            </div>
        
            
            <!-- SUBMIT BUTTON -->
            <div class="container text-center py-5">
                <button class="btn btn-primary submit_btn" name="book" type="submit">Book Now</button>
            </div>
        </div>
      </form>
    <?php } ?>
    </div>
  </main>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
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