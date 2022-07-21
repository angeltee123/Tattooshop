<?php
  session_name("sess_id");
  session_start();

  // navigation guard
  if(!isset($_SESSION['user']['user_id']) || strcasecmp($_SESSION['user']['user_type'], "Admin") != 0){
    Header("Location: ../client/index.php");
    die();
  } else {
    require_once '../api/api.php';
    $api = new api();
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <!-- meta -->
  <?php require_once '../common/meta.php'; ?>
  
  <!-- native style -->
  <title>Analytics | NJC Tattoo</title>
</head>
<body>
  <!-- navigation bar -->
  <?php require_once '../common/header.php'; ?>
  
   <!-- page content -->
  <div class="content w-65">

    <!-- page header -->
    <div class="mb-4">
      <h2 class="fw-bold display-3" style="font-family: 'Yeseva One', cursive, serif;">Analytics</h2>
      <p class="fs-5 text-muted">View live snapshots of your collected business data here.</p>
    </div>

    <!-- analytics -->
    <div class="container border rounded" style="background-color: rgb(252,252,252);">
      <!-- total orders monthly snapshot -->
      <div class="position-relative p-5">
        <h3 class="fw-bold">Total Orders Monthly</h3>
        <p class="text-secondary">This snapshot shows how many tattoo orders have been placed monthly over the last six months.</p>
        <canvas id="monthly_orders" class="my-3"></canvas>
        <script>
          const months = [];
          const orders = [];
        
          <?php
            $month = date("Y-m-d");

            try {           
              for($m = date("Y-m-d", strtotime("-5 months")); $m <= $month; $m = date("Y-m-d", strtotime("+1 month", strtotime($m)))){
                $mo = date("m", strtotime($m));
                $statement = $api->prepare("SELECT COUNT(item_id) FROM (workorder JOIN order_item ON workorder.order_id=order_item.order_id) WHERE MONTH(order_date)=? LIMIT 1");
                if($statement===false){
                  throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $api->bind_params($statement, "i", $mo);
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

                ($api->num_rows($res) > 0) ? $order = $api->fetch_assoc($res) : throw new Exception('An error occured with the server. Please try again later.');

                $api->free_result($statement);
                $mysqli_checks = $api->close($statement);
                ($mysqli_checks===false) ? throw new Exception('The prepared statement could not be closed.') : $statement = null;

                echo "months.push('" . date("F", strtotime($m)) . "');";
                echo "orders.push(" . $order['COUNT(item_id)'] . ");";
              }
            } catch (Exception $e){
              $_SESSION['res'] = $e->getMessage();
              Header("Location: ./index.php");
              exit();
            }
          ?>
        </script>
      </div>

      <!-- page divider -->
      <hr class="mx-5">

      <!-- most frequently ordered tattoos snapshot -->
      <div class="position-relative p-5">
        <h3 class="fw-bold">Most Frequently Ordered Tattoos</h3>
        <p class="text-secondary">This snapshot shows which tattoos in the catalog are most frequently ordered over a year.</p>
        <canvas id="hottest_items" class="my-3"></canvas>
        <script>
          const tattoos = [];
          const quantities = [];
          const colors = [];

          <?php
            try {
              $tattoos = array();
              $mo = date("Y");

              $statement = $api->prepare("SELECT tattoo_id, tattoo_name FROM tattoo");
              if($statement===false){
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
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
                while($row = $api->fetch_assoc($res)){
                  $tattoos = array_merge($tattoos, [$row['tattoo_name'] => $row['tattoo_id']]);
                }
              } else {
                throw new Exception('An error occured with the server. Please try again later.');
              }

              $api->free_result($statement);
              $mysqli_checks = $api->close($statement);
              ($mysqli_checks===false) ? throw new Exception('The prepared statement could not be closed.') : $statement = null;

              foreach($tattoos as $key => $val) {
                $statement = $api->prepare("SELECT COALESCE(SUM(tattoo_quantity), 0) FROM (workorder JOIN (tattoo JOIN order_item ON tattoo.tattoo_id=order_item.tattoo_id) ON workorder.order_id=order_item.order_id) WHERE order_item.tattoo_id=? AND YEAR(order_date)=?");
                if($statement===false){
                  throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $api->bind_params($statement, "si", array($val, $mo));
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

                ($api->num_rows($res) > 0) ? $tattoo = $api->fetch_assoc($res) : throw new Exception('An error occured with the server. Please try again later.');

                $api->free_result($statement);
                $mysqli_checks = $api->close($statement);
                ($mysqli_checks===false) ? throw new Exception('The prepared statement could not be closed.') : $statement = null;

                echo "tattoos.push('" . $key . "');";
                echo "quantities.push(" . $tattoo['COALESCE(SUM(tattoo_quantity), 0)'] . ");";
                echo "colors.push('" . $api->generate_color() . "');";
              }
            } catch (Exception $e){
              $_SESSION['res'] = $e->getMessage();
              Header("Location: ./index.php");
              exit();
            }
          ?>
        </script>
      </div>

      <!-- page divider -->
      <hr class="mx-5">

      <!-- total sales monthly snapshot -->
      <div class="position-relative p-5">
        <h3 class="fw-bold">Total Sales Monthly</h3>
        <p class="text-secondary">This snapshot shows the total amount of sales monthly over the last six months.</p>
        <canvas id="monthly_sales"></canvas>
        <script>
          const sales = [];

          <?php
            try {            
              for($m = date("Y-m-d", strtotime("-5 months")); $m <= $month; $m = date("Y-m-d", strtotime("+1 month", strtotime($m)))){
                $total = (double) 0.00;
                $mo = date("m", strtotime($m));

                $statement = $api->prepare("SELECT COALESCE(amount_paid, 0), COALESCE(payment_change, 0) FROM (workorder JOIN payment ON workorder.order_id=payment.order_id) WHERE MONTH(order_date)=?");
                if($statement===false){
                  throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $api->bind_params($statement, "i", $mo);
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

                while($row = $api->fetch_assoc($res)){
                  $total+= (double) $row['COALESCE(amount_paid, 0)'] - $row['COALESCE(payment_change, 0)'];
                }

                $api->free_result($statement);
                $mysqli_checks = $api->close($statement);
                ($mysqli_checks===false) ? throw new Exception('The prepared statement could not be closed.') : $statement = null;

                echo "sales.push(" . $total . ");";
              }
            } catch (Exception $e){
              $_SESSION['res'] = $e->getMessage();
              Header("Location: ./index.php");
              exit();
            }
          ?>
        </script>
      </div>
    </div>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="./scripts/js/index.js"></script>
</html>