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
        <li><a href="catalogue.php">Catalogue</a></li>
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
  <div class="w-80 mx-auto">
    <h2 class="col fw-bold display-6">Ongoing Workorders</h2>
    <div class="my-5 vstack">
      <?php
        try {
          $join = $api->join("INNER", "workorder", "client", "workorder.client_id", "client.client_id");

          $query = $api->select();
          $query = $api->params($query, array("workorder.client_id", "client_fname", "client_lname", "order_id", "order_date", "amount_due_total", "incentive"));
          $query = $api->from($query);
          $query = $api->table($query, $join);
          $query = $api->where($query, "status", "?");
          $query = $api->order($query, "order_date", "ASC");

          $statement = $api->prepare($query);
          if ($statement===false) {
              throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
          }

          $mysqli_checks = $api->bind_params($statement, "s", "Ongoing");
          if ($mysqli_checks===false) {
              throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
          }

          $mysqli_checks = $api->execute($statement);
          if($mysqli_checks===false) {
              throw new Exception('Execute error: The prepared statement could not be executed.');
          }

          $orders = $api->get_result($statement);
          if($orders===false){
            throw new Exception('get_result() error: Getting result set from statement failed.');
          }
        } catch (Exception $e) {
            exit();
            $_SESSION['res'] = $e->getMessage();
            Header("Location: ./index.php");
        }

        if($api->num_rows($orders) > 0){
          while($order = $api->fetch_assoc($orders)){
      ?>
      <div class="my-4 shadow-sm">
        <div class="collapsible d-flex align-items-center justify-content-between">
          <button type="button" class="text-start bg-none border-0 col p-4" data-bs-toggle="collapse" data-bs-target="#order_<?php echo $api->clean($order['order_id']) ?>" aria-expanded="true" aria-controls="order_<?php echo $api->clean($order['order_id']) ?>">
            <h5 class="d-inline">Order placed by
              <?php
                $datetime = explode('-', $api->clean($order['order_date']));
                $date = date("M:d:Y", strtotime($datetime[0]));
                $time = date("g:i A", strtotime($datetime[1]));
                $date = explode(':', $date);
                echo $api->clean($order['client_fname']) . " " . $api->clean($order['client_lname']);
              ?>
            </h5>
            <p class="d-inline text-muted"><?php echo " on " . $api->clean($date[0]) . " " . $api->clean($date[1]) . ", " . $api->clean($date[2]) ?></p>
          </button>
          <form method="POST" action="./checkout.php" class="w-auto mx-3">
            <input type="hidden" readonly class="d-none" value="<?php echo $api->clean($order['client_id']) ?>" name="client_id" />
            <input type="hidden" readonly class="d-none" value="<?php echo $api->clean($order['order_id']) ?>" name="order_id" />
            <button type="submit" class="btn btn-outline-dark" name="order_checkout">Checkout</button>
          </form>
        </div>
        <div class="collapse show border-top-0 reservation" id="order_<?php echo $api->clean($order['order_id']) ?>">
          <div class="border-bottom row p-5">
            <!-- order id -->
            <div class="col">
              <label class="form-label fw-semibold">Order ID</label>
              <p><?php echo $api->clean($order['order_id']) ?></p>
            </div>
            <!-- order datetime -->
            <div class="col">
              <label class="form-label fw-semibold">Placed on</label>
              <p><?php echo $api->clean($date[0]) . " " . $api->clean($date[1]) . ", " . $api->clean($date[2]) . ", " . $api->clean($time) ?></p>
            </div>
            <!-- amount due total -->
            <div class="col">
              <label for="status" class="form-label fw-semibold">Amount Due Total</label>
              <p>Php <?php echo $api->clean($order['amount_due_total']) ?></p>
            </div>
            <!-- incentive -->
            <div class="col">
              <label class="form-label fw-semibold">Incentive</label>
              <p><?php echo $api->clean($order['incentive']) ?></p>
            </div>
          </div>
          <?php
            try {    
              $get_items = $api->prepare("SELECT order_item.item_id, tattoo_name, tattoo_image, tattoo_price, tattoo_quantity, order_item.tattoo_width, order_item.tattoo_height, paid, item_status, amount_addon FROM ((order_item INNER JOIN tattoo ON order_item.tattoo_id=tattoo.tattoo_id) LEFT JOIN reservation ON order_item.item_id=reservation.item_id) WHERE order_id=? ORDER BY paid ASC, item_status DESC");
              if ($get_items===false) {
                  throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
              }
    
              $mysqli_checks = $api->bind_params($get_items, "s", $order['order_id']);
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

            if($api->num_rows($items) > 0){
              while($item = $api->fetch_assoc($items)){
          ?>
          <form action="./queries.php" method="POST">
            <div class="border-bottom d-flex align-items-center justify-content-between p-5">
              <!-- tattoo image -->
              <div class="tattoo-image shadow-sm border-2 rounded" style="background-image: url(<?php echo $api->clean($item['tattoo_image']); ?>)"></div>
              <div class="w-100 ms-6">
                <div class="row my-5">
                  <!-- tattoo name -->
                  <div class="col">
                    <label class="form-label fw-semibold">Item</label>
                    <p><?php echo $api->clean($item['tattoo_name']) ?></p>
                    <input type="hidden" readonly class="d-none" value="<?php echo $api->clean($item['item_id']) ?>" name="item_id" />
                  </div>
                  <!-- item status -->
                  <div class="col">
                    <label for="status" class="form-label fw-semibold">Item Status</label>
                    <p><?php echo $api->clean($item['item_status']) ?></p>
                  </div>
                  <!-- amount_addon -->
                  <div class="col">
                    <label for="status" class="form-label fw-semibold">Amount Addon</label>
                    <p><?php echo empty($item['amount_addon']) ? "N/A" : $api->clean($item['amount_addon']); ?></p>
                  </div>
                  <!-- payment status -->
                  <div class="col">
                    <label class="form-label fw-semibold">Payment Status</label>
                    <p><?php echo $api->clean($item['paid']); ?></p>
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
                    <?php if(in_array($item['item_status'], array("Applied", "Reserved"))){ ?>
                      <p><?php echo $api->clean($item['tattoo_quantity']) ?></p>
                    <?php } else { ?>
                      <input type="number" class="form-control" value="<?php echo $api->clean($item['tattoo_quantity']) ?>" min="1" name="quantity" />
                    <?php } ?>
                  </div>
                  <!-- width -->
                  <div class="col">
                    <label for="width" class="form-label fw-semibold">Width</label>
                    <?php if(in_array($item['item_status'], array("Applied", "Reserved"))){ ?>
                      <p><?php echo $api->clean($item['tattoo_width']) ?></p>
                    <?php } else { ?>
                      <input type="number" class="form-control" value="<?php echo $api->clean($item['tattoo_width']) ?>" min="1" name="width" />
                    <?php } ?>
                  </div>
                  <!-- height --->
                  <div class="col">
                    <label for="height" class="form-label fw-semibold">Height</label>
                    <?php if(in_array($item['item_status'], array("Applied", "Reserved"))){ ?>
                      <p><?php echo $api->clean($item['tattoo_height']) ?></p>
                    <?php } else { ?>
                      <input type="number" class="form-control" value="<?php echo $api->clean($item['tattoo_height']) ?>" min="1" name="height" />
                    <?php } ?>
                  </div>
                </div>
                <?php if(!in_array($item['item_status'], array("Applied", "Reserved"))){ ?>
                  <div class="row">
                    <div class="col">
                      <button type="submit" class="me-1 btn btn-outline-primary" name="update_item">Update</button>
                      <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#delete_<?php echo $item['item_id'] ?>">Remove Item</button>
                    </div>
                  </div>
                <?php } ?>
              </div>
            </div>
          </form>
          <!-- deletion-modal -->
          <div class="modal fade" id="delete_<?php echo $item['item_id'] ?>" tabindex="-1" aria-labelledby="delete_<?php echo $item['item_id'] ?>_label" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="#delete_<?php echo $item['item_id'] ?>_label">Confirm Removal</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <p>Are you sure you want to remove this item from the workorder?</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <form action="./queries.php" method="POST">
                    <input type="hidden" class="d-none" name="item_id" value="<?php echo $item['item_id'] ?>">
                    <button type="submit" class="btn btn-outline-danger" name="delete_item">Yes, remove it</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <?php
              }
            } else { ?>
              <div class="p-5"><h1 class="m-3 display-4 fst-italic text-muted">No tattoos ordered.</h1></div>
          <?php } ?>
        </div>
      </div>
      <?php
        }
      } else {
      ?>
      <h1 class="my-5 display-6 fst-italic text-muted">No ongoing workorders.</h1>
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
  </div>  
</body>
<script src="../api/bootstrap-bundle-min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> -->
</html>
<?php
  if(isset($_SESSION['res'])){
    unset($_SESSION['res']);
  }
?>