<?php
  session_name("sess_id");
  session_start();
  if(!isset($_SESSION['user_id'])){
    Header("Location: ./index.php");
    die();
  } else {
    require_once '../api/api.php';
    $api = new api();
  }

  try {
    $client_id = $_SESSION['client_id'];
    $mysqli_checks = $api->get_workorder($client_id);
    if ($mysqli_checks!==true) {
      throw new Exception('Error: Retrieving client workorder failed.');
    } else {
      $order_id = $_SESSION['order_id'];
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
      max-width: 275x;
      max-height: 275px;
      width: 275px;
      height: 275px;
      background-position: center;
      background-repeat: no-repeat;
      background-size: cover;
    }
  </style>
  <title>Orders | NJC Tattoo</title>
</head>
<body class="w-100">
  <header class="header border-bottom border-2">
    <nav class="nav-bar row mx-0">
      <ul class="col my-0" id="nav-links">
        <li><a href="explore.php">Explore</a></li>
        <li class="active"><a href="orders.php">Orders</a></li>
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
  <div class="w-80 my-9 mx-auto">
    <?php
      try {
        // retrieving workorder details
        $query = $api->select();
        $query = $api->params($query, array("order_date", "amount_due_total", "incentive"));
        $query = $api->from($query);
        $query = $api->table($query, "workorder");
        $query = $api->where($query, "order_id", "?");

        $statement = $api->prepare($query);
        if ($statement===false) {
            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
        }

        $mysqli_checks = $api->bind_params($statement, "s", $order_id);
        if ($mysqli_checks===false) {
            throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
        }

        $mysqli_checks = $api->execute($statement);
        if($mysqli_checks===false) {
            throw new Exception('Execute error: The prepared statement could not be executed.');
        }

        $order = $api->get_result($statement);
        if($order===false){
          throw new Exception('get_result() error: Getting result set from statement failed.');
        }
      } catch (Exception $e) {
          exit();
          $_SESSION['res'] = $e->getMessage();
          Header("Location: ./index.php");
      }

      if($api->num_rows($order) > 0){
        $workorder = $api->fetch_assoc($order);

        $datetime = explode('-', $api->clean($workorder['order_date']));
        $date = date("M:d:Y", strtotime($datetime[0]));
        $time = date("g:i A", strtotime($datetime[1]));
        $date = explode(':', $date);
    ?>
      <div class="border shadow-sm">
        <div class="row p-5">
          <!-- order id -->
          <div class="col">
            <label class="form-label fw-semibold">Order ID</label>
            <p><?php echo $api->clean($order_id) ?></p>
          </div>
          <!-- order datetime -->
          <div class="col">
            <label class="form-label fw-semibold">Placed on</label>
            <p><?php echo $api->clean($date[0]) . " " . $api->clean($date[1]) . ", " . $api->clean($date[2]) . ", " . $api->clean($time) ?></p>
          </div>
          <!-- amount due total -->
          <div class="col">
            <label for="status" class="form-label fw-semibold">Amount Due Total</label>
            <p>Php <?php echo $api->clean($workorder['amount_due_total']) ?></p>
          </div>
          <!-- incentive -->
          <div class="col">
            <label class="form-label fw-semibold">Incentive</label>
            <p><?php echo $api->clean($workorder['incentive']) ?></p>
          </div>
        </div>
        <form method="POST" action="../api/queries.php">
        <?php
          try { 
            $get_items = $api->prepare("SELECT order_item.item_id, tattoo_name, tattoo_image, tattoo_price, tattoo_quantity, order_item.tattoo_width, order_item.tattoo_height, paid, item_status, amount_addon FROM ((order_item INNER JOIN tattoo ON order_item.tattoo_id=tattoo.tattoo_id) LEFT JOIN reservation ON order_item.item_id=reservation.item_id) WHERE order_id=? ORDER BY item_status ASC, paid ASC");
            if ($get_items===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }

            $mysqli_checks = $api->bind_params($get_items, "s", $order_id);
            if ($mysqli_checks===false) {
                throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
            }

            $mysqli_checks = $api->execute($get_items);
            if($mysqli_checks===false) {
                throw new Exception('Execute error: The prepared statement could not be executed.');
            }

            $items = $api->get_result($get_items);
            if($items===false){
              throw new Exception('get_result() error: Getting result set from statement failed.');
            }
          } catch (Exception $e) {
              exit();
              $_SESSION['res'] = $e->getMessage();
              Header("Location: ./index.php");
          }
        
          if($api->num_rows($items) > 0) {
        ?>
          <div class="row mx-0 border-top bg-extralight py-3 px-5">
            <div class="col-md-7 d-flex justify-content-start align-items-center">
              <h5 class="fst-italic text-muted">Tick the checkboxes for the items you want to modify or remove from your order.</h5>
            </div>
            <div class="col d-flex justify-content-end align-items-center">
              <button type="button" id="select-all" class="btn btn-link text-black text-decoration-none me-2">Select All</button>
              <a href="checkout.php" class="btn btn-outline-dark me-2">Checkout</a>
              <button type="submit" class="btn btn-outline-primary me-2" name="update_items">Update Items</button>
              <button type="submit" class="btn btn-outline-danger" name="remove_items">Remove Items</button>
            </div>
          </div>
        <?php }

          if($api->num_rows($items) > 0){
            while($item = $api->fetch_assoc($items)){
        ?>
          <div class="border-top d-flex align-items-center justify-content-between p-5">
            <!-- checkbox -->
            <?php if((strcasecmp($item['item_status'], "Standing") == 0 && strcasecmp($item['paid'], "Unpaid") == 0)){ ?>
              <div class="me-4">
                <input type="hidden" class="d-none" name="index[]" value="<?php echo $item['item_id']?>" />
                <input type="checkbox" class="form-check-input p-2" name="item[]" value="<?php echo $item['item_id']?>"/>
              </div>
            <?php } ?>
            <!-- tattoo image -->
            <div class="tattoo-image shadow-sm border-2 rounded" style="background-image: url(<?php echo $api->clean($item['tattoo_image']); ?>)"></div>
            <div class="w-100 ms-6">
              <div class="row my-5">
                <!-- tattoo name -->
                <div class="col">
                  <label class="form-label fw-semibold">Item</label>
                  <p><?php echo $api->clean($item['tattoo_name']) ?></p>
                </div>
                <!-- item status -->
                <div class="col">
                  <label for="status" class="form-label fw-semibold">Item Status</label>
                  <p><?php echo $api->clean($item['item_status']) ?></p>
                  <?php if((strcasecmp($item['item_status'], "Standing") == 0 && strcasecmp($item['paid'], "Unpaid") == 0)){ ?>
                    <input type="hidden" class="d-none" name="status[]" value="<?php echo $item['item_status']?>" />
                  <?php } ?>
                </div>
                <!-- amount_addon -->
                <div class="col">
                  <label for="status" class="form-label fw-semibold">Amount Addon</label>
                  <p><?php echo ($item['amount_addon'] == 0) ? "N/A" : "₱" . $api->clean($item['amount_addon']); ?></p>
                </div>
                <!-- payment status -->
                <div class="col">
                  <label class="form-label fw-semibold">Payment Status</label>
                  <p><?php echo $api->clean($item['paid']); ?></p>
                  <?php if((strcasecmp($item['item_status'], "Standing") == 0 && strcasecmp($item['paid'], "Unpaid") == 0)){ ?>
                    <input type="hidden" class="d-none" name="paid[]" value="<?php echo $item['paid']?>" />
                  <?php } ?>
                </div>
              </div>
              <div class="row my-5">
                <!-- price -->
                <div class="col">
                  <label for="quantity" class="form-label fw-semibold">Price</label>
                  <p>₱<?php echo $api->clean($item['tattoo_price']) ?></p>
                </div>
                <!-- quantity -->
                <div class="col">
                  <label for="quantity" class="form-label fw-semibold">Quantity</label>
                  <?php if((strcasecmp($item['item_status'], "Standing") == 0 && strcasecmp($item['paid'], "Unpaid") == 0)){ ?>
                    <input type="number" class="form-control" value="<?php echo $api->clean($item['tattoo_quantity']) ?>" min="1" name="quantity[]" />
                  <?php } else { ?>
                    <p><?php echo $api->clean($item['tattoo_quantity']) ?></p>
                  <?php } ?>
                </div>
                <!-- width -->
                <div class="col">
                  <label for="width" class="form-label fw-semibold">Width</label>
                  <?php if((strcasecmp($item['item_status'], "Standing") == 0 && strcasecmp($item['paid'], "Unpaid") == 0)){ ?>
                    <input type="number" class="form-control" value="<?php echo $api->clean($item['tattoo_width']) ?>" min="1" name="width[]" />
                  <?php } else { ?>
                    <p><?php echo $api->clean($item['tattoo_width']) ?></p>
                  <?php } ?>
                </div>
                <!-- height --->
                <div class="col">
                  <label for="height" class="form-label fw-semibold">Height</label>
                  <?php if((strcasecmp($item['item_status'], "Standing") == 0 && strcasecmp($item['paid'], "Unpaid") == 0)){ ?>
                    <input type="number" class="form-control" value="<?php echo $api->clean($item['tattoo_height']) ?>" min="1" name="height[]" />
                  <?php } else { ?>
                    <p><?php echo $api->clean($item['tattoo_height']) ?></p>
                  <?php } ?>
                </div>
              </div>
            </div>
          </div>
        <?php
            }
          } else { ?>
            <div class="p-5 border-top"><h1 class="m-3 display-4 fst-italic text-muted">No tattoos ordered.</h1></div>
        <?php }
          try {
            $api->free_result($get_items);
        
            $mysqli_checks = $api->close($get_items);
            if ($mysqli_checks===false) {
                throw new Exception('The prepared statement could not be closed.');
            }
          } catch (Exception $e) {
            echo $e->getMessage();
            Header("Location: ./index.php");
          }
        ?>
        </form>
      </div>
      <?php
        } else {
      ?>
      <div class="w-100 text-center"><h1 class="m-3 display-2 fst-italic text-muted">You currently have no standing workorder.</h1></div>
      <?php 
        }
      
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
</body>
<script>
  var all_selected = false;
  var select_all = document.getElementById('select-all');
  var checkboxes = document.getElementsByClassName('form-check-input');

  select_all.addEventListener('mouseover', function() {
    this.classList.remove('text-decoration-none');
  });

  select_all.addEventListener('mouseout', function() {
    this.classList.add('text-decoration-none');
  });

  select_all.addEventListener('click', function() {
    all_selected = !all_selected;
    all_selected ? this.innerText = "Deselect" : this.innerText = "Select All";

    for(var i=0, count=checkboxes.length; i < count; i++){
      checkboxes[i].checked = all_selected;
    }
  });
</script>
<script src="../api/bootstrap-bundle-min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> -->
</html>