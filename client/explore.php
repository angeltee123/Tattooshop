<?php
  session_name("sess_id");
  session_start();
  if(!isset($_SESSION['user_id'])){
    Header("Location: ../client/index.php");
    die();
  } else {
    require_once '../api/api.php';
    $api = new api();
  }
    
  if(!isset($_SESSION['user_id'])){
    try {
      $client_id = $_SESSION['client_id'];
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
          unset($_SESSION['order_id']);
      }
  
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
    <link href="./style/explore.css" rel="stylesheet" scoped>
    <link href="./style/style.css" rel="stylesheet">
    <title>Explore | NJC Tattoo</title>
</head>
<body class="w-100">
  <header class="header border-bottom border-2">
    <nav class="nav-bar row mx-0">
      <ul class="col my-0" id="nav-links">
        <li class="active"><a href="explore.php">Explore</a></li>
        <li><a href="orders.php">Orders</a></li>
        <li><a href="reservations.php">Bookings</a></li>
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
  <div class="content mx-auto my-5" style="width: 68% !important">
    <h2 class="fw-bold display-6 mb-4">Explore Tattoos</h2>
    <div class="w-100 d-flex flex-row flex-wrap justify-content-between align-items-start">
      <?php
        $query = $api->select();
        $query = $api->params($query, "*");
        $query = $api->from($query);
        $query = $api->table($query, "tattoo");
        $query = $api->order($query, "cataloged", "ASC");

        try {
          $statement = $api->prepare($query);
          if($statement===false){
            throw new Exception("prepare() error: The statement could not be prepared.");
          }
      
          $mysqli_checks = $api->execute($statement);
          if($mysqli_checks===false){
            throw new Exception('Execute error: The prepared statement could not be executed.');
          }
      
          $res = $api->get_result($statement);
          if($res===false){
            throw new Exception('get_result() error: Getting result set from statement failed.');
          }
        } catch (Exception $e){
          exit;
          Header("Location: ./index.php");
          echo $e->getMessage();
        }

        $count = 0;

        if($api->num_rows($res)){
          while($row = $api->fetch_assoc($res)){
            $id = $api->clean($row['tattoo_id']);
            $name = $api->clean($row['tattoo_name']);
            $price = $row['tattoo_price'];
            $height = $row['tattoo_height'];
            $width = $row['tattoo_width'];
            $image = $row['tattoo_image'] ;
            $description = $api->clean($row['tattoo_description']);
            $color_scheme = $api->clean($row['color_scheme']);
            $complexity = $api->clean($row['complexity_level']);  
      ?>
        <a class="order-<?php echo $count++?> tattoo-card my-4 d-block border-secondary border-2 rounded" href="#<?php echo $name?>" style="background-image: url(<?php echo $api->clean($image); ?>)"></a>
        <!-- tattoo-modal -->
        <div id="<?php echo $name?>" class="tattoo_detail align-items-center justify-content-center">
          <div class="cont row">
            <div class="order-first col-5 h-100 tattoo-image" style="background-image: url(<?php echo $api->clean($image); ?>)"></div>
            <div class="order-last col p-5 h-100 d-flex flex-column position-relative">
              <div class="position-absolute end-0 me-5">
                <a href="" class="float-end"><span class="material-icons md-48">close</span></a>
              </div>
              <div class="my-4 tattoo-header">
                <h2 class="display-4 fw-bold"><?php echo $name ?></h2>
                <h2 class="text-secondary"><?php echo "₱".$price ?></h2>
              </div>
              <div class="my-3 tattoo-description">
                <p class="text-wrap"><?php echo $description ?></p>
              </div>
              <div class="my-3 row">
                <div class="tattoo-color col">
                  <h3>Color</h3>
                  <div class="d-flex flex-row align-items-center">
                    <?php if(strcasecmp($color_scheme, "Monochrome") == 0){ ?>
                      <div class="color border border-4 rounded-pill" style="background-color: #000000"></div>
                    <?php } else { ?>
                      <div class="color border border-4 rounded-pill" style="background-image: linear-gradient(to bottom right, #FF00FF, blue)"></div>
                    <?php } ?>
                    <p class="color-tooltip d-inline p-2 my-0 ms-2 w-auto bg-dark rounded"><?php if(strcasecmp($color_scheme, "Monochrome") == 0){ echo "Monochrome"; } else { echo "Multicolor"; } ?></p>
                  </div>
                </div>
                <div class="tattoo-complexity col">
                  <h3>Complexity</h3>
                  <p><?php echo $complexity ?></p>
                </div>
                <div class="col"></div>
              </div>
              <form action="../api/queries.php" method="post" class="d-block my-3 h-100 w-100">
                <input type="hidden" class="d-none" name="tattoo_name" value="<?php echo $id ?>">
                <input type="hidden" class="d-none" name="tattoo_id" value="<?php echo $id ?>">
                <div class="row">
                  <h2>Dimensions</h2>
                  <div class="col">
                    <label for="<?php echo $name ?>_width"><p class="fs-5">Width (in inches)</p></label>
                    <input type="number" class="form-control w-25" id="<?php echo $name ?>_width" value="<?php echo $width ?>" placeholder="Width" min="1" max="24" name="width" required>
                  </div>
                  <div class="col">
                    <label for="<?php echo $name ?>_height"><p class="fs-5">Height (in inches)</p></label>
                    <input type="number" class="form-control w-25" id="<?php echo $name ?>_height" value="<?php echo $height ?>" placeholder="Height" min="1" max="36" name="height" required>
                  </div>
                </div>
                <div class="row my-3">
                  <div class="col">
                    <label><h3>Quantity</h3></label>
                    <input type="number" class="form-control input-quantity" min="1" value="1" placeholder="Quantity" name="quantity" required>
                  </div>
                </div>
                <div class="row mt-5">
                  <div class="col">
                    <button type="submit" class="btn btn-lg btn-dark w-100 d-inline" name="order_item">Add to Order</button> 
                  </div>
                </div> 
              </form>
            </div>
          </div>
      </div>
      <?php    
          }
        }
      ?>
    </div>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</html>
<?php
  if(isset($_SESSION['width_err'])){
    unset($_SESSION['width_err']);
  }
  if(isset($_SESSION['height_err'])){
    unset($_SESSION['height_err']);
  }
  if(isset($_SESSION['quantity_err'])){
    unset($_SESSION['quantity_err']);
  }
  if(isset($_SESSION['res'])){
    unset($_SESSION['res']);
  }
?>