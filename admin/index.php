<?php
  session_name("sess_id");
  session_start();
  // if(!isset($_SESSION['user']['user_id']) || strcasecmp($_SESSION['user']['user_type'], "Admin") != 0){
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
  <?php require_once '../common/meta.php'; ?>
  <!-- native style -->
  <title>Analytics | NJC Tattoo</title>
</head>
<body class="w-100">
  <?php require_once '../common/header.php'; ?>
  <div class="content">
    <div>
      <h2 class="fw-bold display-3">Analytics</h2>
      <p class="fs-5 text-muted">Manage your catalogue of tattoos and add new tattoos to it.</p>
    </div>
    <div class="container">
      <canvas id="monthly_orders"></canvas>
      <script>
        const months = [];
        const orders = [];
      
        <?php
          try {
            $month = date("Y-m-d");
            
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
      <canvas id="hottest_items"></canvas>
      <script>
        const tattoos = [];
        const quantities = [];
        const colors = [];

        <?php
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
        ?>
      </script>
    </div>
  </div>
</body>
<script src="../api/api.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const total_orders_monthly_data = {
    labels: months,
    datasets: [{
      label: 'Total Orders',
      backgroundColor: '#ff6384',
      borderColor: '#ff6384',
      data: orders,
      tension: 0.3
    }]
  };

  const hottest_items_data = {
    labels: tattoos,
    datasets: [{
      label: 'Most Ordered Tattoos',
      data: quantities,
      backgroundColor: colors,
      hoverOffset: 4
    }]
  };

  const total_orders_monthly_config = {
    type: 'line',
    data: total_orders_monthly_data,
    options: {}
  };

  const hottest_items_config = {
    type: 'doughnut',
    data: hottest_items_data,
  };

  const total_orders_monthly = new Chart(
    document.getElementById('monthly_orders'),
    total_orders_monthly_config
  );

  const myChart = new Chart(
    document.getElementById('hottest_items'),
    hottest_items_config
  );
</script>
</html>