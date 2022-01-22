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

  if(!isset($_SESSION['order_id']) || !empty($_SESSION['order_id'])){
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
        Header("Location: ./client/index.php");
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
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
  
  <!-- native style -->
  <link href="./style/bootstrap.css" rel="stylesheet">
  <link href="./style/style.css" rel="stylesheet">
  <link href="./style/orders.css" rel="stylesheet">
  <title>Orders | NJC Tattoo</title>
</head>
<body>
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
  <form method="POST" action="../api/queries.php">
    <button type="button" id="orders select-all" class="btn btn-link">Select All</button>
    <button type="submit" class="btn btn-outline-primary" name="update_items">Update</button>
    <button type="submit" class="btn btn-outline-danger" name="remove_items">Delete</button>
    <table class="table w-75 mx-auto">
      <thead class="align-middle" style="height: 4em;">
        <tr>
          <th scope="col"></th>
          <th scope="col">Status</th>
          <th scope="col">Payment Status</th>
          <th scope="col">Tattoo</th>
          <th scope="col">Width</th>
          <th scope="col">Height</th>
          <th scope="col">Quantity</th>
          <th scope="col">Price</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $amount_total = (double) 0.00;

          // $left_table = $api->join("INNER", "order_item", "tattoo", "order_item.tattoo_id","tattoo.tattoo_id");
          // $joined_table = $api->join("INNER", $left_table, "workorder", "workorder.order_id","order_item.order_id");
          $query = $api->select();
          // $query = $api->params($query, array("workorder.order_id","order_item.item_id","workorder.order_date","tattoo.tattoo_id","tattoo.tattoo_name","tattoo.tattoo_description","order_item.tattoo_quantity","tattoo.tattoo_price"));
          $query = $api->params($query, "*");
          $query = $api->from($query);
          // $query = $api->table($query, $joined_table);
          $query = $api->table($query, "orders");
          $query = $api->where($query, array("client_id", "order_id"), array("?", "?"));
          $not_condition = "AND tattoo_quantity > ? AND item_status NOT IN (?, ?)";
          $query = $query . $not_condition;

          try {
            $statement = $api->prepare($query);
            if($statement===false){
              throw new Exception("prepare() error: The statement could not be prepared.");
            }

            $mysqli_checks = $api->bind_params($statement, "ssiss", array($_SESSION['client_id'], $_SESSION['order_id'], 0, "Reserved", "Completed"));
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
          } catch (Exception $e) {
            echo $e->getMessage();
            // Header("Location: ./orders.php");
          }

          if($api->num_rows($res) > 0){
            while($row = $api->fetch_assoc($res)){
              if(strcasecmp($row['paid'], 'Unpaid') == 0){
                $amount_total += $row['tattoo_price'] * $row['tattoo_quantity'];
              }
        ?>
        <tr class="align-middle" style="height: 300px;">
          <td scope="row">
            <input type="hidden" class="d-none" name="index[]" value="<?php echo $row['item_id']?>" />
            <input type="checkbox" class="orders form-check-input" name="item[]" value="<?php echo $row['item_id']?>"/>
          </td>
          <td>
            <?php echo $api->clean($row['item_status']) ?>
            <input type="hidden" class="d-none" name="status[]" value="<?php echo $row['item_status']?>" />
          </td>
          <td>
            <?php echo $api->clean($row['paid']) ?>
            <input type="hidden" class="d-none" name="paid[]" value="<?php echo $row['paid']?>" />
          </td>
          <td>
            <div class="d-flex flex-row align-items-center">
              <div class="tattoo-image border-2 rounded" style="background-image: url(<?php echo $api->clean($row['tattoo_image']); ?>)"></div>
              <div class="w-auto mx-3">
                <p class="fw-bold d-inline w-auto"><?php echo $api->clean($row['tattoo_id']) ?></p>
                <p class="fw-bold d-inline w-auto"><?php echo $api->clean($row['tattoo_name']); ?></p>
              </div>
            </div>
          </td>
          <td><input type="number" class="form-control" name="width[]" min="1" value="<?php echo $row['tattoo_width']?>"/>
          <td><input type="number" class="form-control" name="height[]" min="1" value="<?php echo $row['tattoo_height']?>"/>
          <td><input type="number" class="form-control" name="quantity[]" min="1" value="<?php echo $row['tattoo_quantity']?>"/></td>
          <td>Php <?php echo number_format($row['tattoo_price'], 2, '.', '') ?><input type="hidden" class="d-none" name="price[]" value="<?php echo number_format($row['tattoo_price'], 2, '.', '') ?>"/></td>
        </tr>
        <?php } ?>
        <tfoot style="height: 4em;">
          <td colspan="6"></td>
          <td class="fw-bold">Amount Total</td>
          <td>Php <?php echo number_format($amount_total, 2, '.', '') ?></td>
        </tfoot>
        <?php } else { ?>
          <tfoot>
            <td colspan="8" class="p-5"><h1 class="m-3 display-4 fst-italic text-muted">No tattoos ordered.</h1></td>
          </tfoot>
        <?php } ?>
      </tbody>
    </table>
  </form>
  <table class="table w-75 mx-auto">
      <thead class="align-middle" style="height: 4em;">
        <tr>
          <th scope="col">Reservation Status</th>
          <th scope="col">Paid</th>
          <th scope="col">Scheduled Date</th>
          <th scope="col">Tattoo</th>
          <th scope="col">Width</th>
          <th scope="col">Height</th>
          <th scope="col">Quantity</th>
          <th scope="col">Reservation Addon</th>
          <th scope="col">Price</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $amount_total = (double) 0.00;

          $left_table = $api->join("INNER", "order_item", "tattoo", "order_item.tattoo_id","tattoo.tattoo_id");
          $right_table = $api->join("INNER", $left_table, "workorder", "workorder.order_id","order_item.order_id");
          $joined_table = $api->join("INNER", $right_table, "reservation", "reservation.item_id", "order_item.item_id");

          $query = $api->select();
          $query = $api->params($query, array("reservation_status", "paid", "reservation.scheduled_date", "tattoo.tattoo_id", "tattoo.tattoo_name", "tattoo.tattoo_image", "tattoo.tattoo_description", "order_item.tattoo_width", "order_item.tattoo_height", "order_item.tattoo_quantity", "tattoo.tattoo_price", "amount_addon"));
          $query = $api->from($query);
          $query = $api->table($query, $joined_table);
          $query = $api->where($query, array("client_id", "workorder.order_id", "item_status"), array("?", "?", "?"));

          try {
            $statement = $api->prepare($query);
            if($statement===false){
              throw new Exception("prepare() error: The statement could not be prepared.");
            }

            $mysqli_checks = $api->bind_params($statement, "sss", array($_SESSION['client_id'], $_SESSION['order_id'], "Reserved"));
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
          } catch (Exception $e) {
            echo $e->getMessage();
            // Header("Location: ./orders.php");
          }

          if($api->num_rows($res) > 0){
            while($row = $api->fetch_assoc($res)){
              if(strcasecmp($row['paid'], 'Unpaid') == 0){
                $amount_total += ($row['tattoo_price'] * $row['tattoo_quantity']) + $row['amount_addon'];
              } else if(strcasecmp($row['paid'], 'Partially Paid') == 0){
                $amount_total += $row['amount_addon'];
              }
        ?>
        <tr class="align-middle" style="height: 300px;">
        <td><?php echo $api->clean($row['reservation_status']) ?></td>
          <td><?php echo $api->clean($row['paid']) ?></td>
          <td><?php echo $api->clean($row['scheduled_date']) ?></td>
          <td>
            <div class="d-flex flex-row align-items-center">
              <div class="tattoo-image border-2 rounded" style="background-image: url(<?php echo $api->clean($row['tattoo_image']); ?>)"></div>
              <div class="w-auto mx-3">
                <p class="fw-bold d-inline w-auto"><?php echo $api->clean($row['tattoo_name']); ?></p>
              </div>
            </div>
          </td>
          <td><?php echo $row['tattoo_width'] ?></td>
          <td><?php echo $row['tattoo_height'] ?></td>
          <td><?php echo $row['tattoo_quantity'] ?></td>
          <td>Php <?php echo number_format($row['amount_addon'], 2, '.', '') ?></td>
          <td>Php <?php echo number_format($row['tattoo_price'] * $row['tattoo_quantity'], 2, '.', '') ?></td>
        </tr>
        <?php } ?>
        <tfoot style="height: 4em;">
          <td colspan="7"></td>
          <td class="fw-bold">Amount Total</td>
          <td>Php <?php echo number_format($amount_total, 2, '.', '') ?></td>
        </tfoot>
        <?php } else { ?>
          <tfoot>
            <td colspan="7" class="p-5"><h1 class="m-3 display-4 fst-italic text-muted">No tattoos reserved.</h1></td>
          </tfoot>
        <?php } ?>
      </tbody>
    </table>
</body>
<script>
  var all_selected = false;
  var select_all = document.getElementById('orders select-all');
  var checkboxes = document.getElementsByClassName('orders form-check-input');

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