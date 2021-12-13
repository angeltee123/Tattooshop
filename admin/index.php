<?php
	session_name("sess_id");
	session_start();
	// if(!isset($_SESSION['user_id']) || strcasecmp($_SESSION['user_type'], 'User') == 0){
	// 	Header("Location: ../client/index.php");
	// 	die();
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
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Yeseva+One&display=swap" rel="stylesheet">

	<!-- bootstrap -->
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
	
	<!-- native style -->
	<link href="../client/style/style.css" rel="stylesheet">
	<title>Admin | NJC Tattoo</title>
</head>
<body>
  <ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item" role="presentation">
      <button class="nav-link p-3" id="reservations-tab" data-bs-toggle="tab" data-bs-target="#reservations" type="button" role="tab" aria-controls="reservations" aria-selected="false">Reservations</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link p-3" id="workorders-tab" data-bs-toggle="tab" data-bs-target="#workorders" type="button" role="tab" aria-controls="workorders" aria-selected="false">Workorders</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link p-3 active" id="clients-tab" data-bs-toggle="tab" data-bs-target="#clients" type="button" role="tab" aria-controls="clients" aria-selected="true">Clients</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link p-3" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab" aria-controls="users" aria-selected="false">Users</button>
    </li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane fade" id="reservations" role="tabpanel" aria-labelledby="reservations-tab">...</div>
    <div class="tab-pane fade" id="workorders" role="tabpanel" aria-labelledby="workorders-tab">
      <h1 class="display-6">Ongoing Workorders</h1>
      <?php
        try {
          $get_workorders = $api->prepare("SELECT * FROM workorder WHERE status=?");
          if($get_workorders===false){
            throw new Exception("prepare() error: The statement could not be prepared.");
          }

          $mysqli_checks = $api->bind_params($get_workorders, "s", "Ongoing");
          if($mysqli_checks===false){
            throw new Exception('Execute error: The prepared statement could not be executed.');
          }
      
          $mysqli_checks = $api->execute($get_workorders);
          if($mysqli_checks===false){
            throw new Exception('Execute error: The prepared statement could not be executed.');
          }
      
          $res = $api->get_result($get_workorders);
          if($res===false){
            throw new Exception('get_result() error: Getting result set from statement failed.');
          }
        } catch (Exception $e){
          exit;
          echo $e->getMessage();
        }
      ?>
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="change_workorder_rows" />
        <label class="form-check-label" for="change_workorder_rows" id="workorder_editing_label">Edit</label>
      </div>
      <button type="button" id="workorder_form select-all" class="btn btn-link">Select All</button>
      <form method="POST" action="./queries.php">
        <button type="submit" class="btn btn-outline-primary" name="update_workorder">Update</button>
        <button type="submit" class="btn btn-outline-danger" name="delete_workorder">Delete</button>
        <table class="table w-100">
          <thead class="align-middle" style="height: 4em;">
            <tr>
              <th scope="col"></th>
              <th scope="col">order_id</th>
              <th scope="col">client_id</th>
              <th scope="col">order_date</th>
              <th scope="col">amount_due</th>
              <th scope="col">incentive</th>
              <th scope="col">status</th>
            </tr>
          </thead>
          <tbody>
            <?php
              if($api->num_rows($res) > 0){
                while($row = $api->fetch_assoc($res)){
            ?>
            <tr class="align-middle" style="height: 5em;">
              <td scope="row"><input type="checkbox" class="workorder form-check-input" name="item[]" value="<?php echo $row['order_id']?>"/></td>
              <th><input type="text" readonly class="form-control-plaintext" name="order_id[]" value="<?php echo $api->clean($row['order_id']) ?>"/></th>
              <td><input type="text" readonly class="form-control-plaintext" name="client_id[]" value="<?php echo $api->clean($row['client_id']) ?>" required/></td>
              <td><input type="date" readonly class="form-control-plaintext" name="order_date[]" value="<?php echo $row['order_date'] ?>" required/></td>
              <td><input type="text" inputmode="numeric" readonly class="workorder form-control-plaintext" name="amount_due[]" value="<?php echo $row['amount_due'] ?>" required/></td>
              <td>
                <select class="form-select" name="incentive" disabled>
                  <option value="None" <?php if(strcasecmp($api->clean($row['incentive']), 'None') == 0){ echo 'selected'; } ?>>None</option>
                  <option value="Free 3x3 Tattoo" <?php if(strcasecmp($api->clean($row['incentive']), 'Free 3x3 Tattoo') == 0){ echo 'selected'; } ?>>Free 3x3 Tattoo</option>
                  <option value="15% Discount" <?php if(strcasecmp($api->clean($row['incentive']), '15% Discount') == 0){ echo 'selected'; } ?>>15% discount</option>
                </select>
              </td>
              <td>
                <select class="form-select" name="status" disabled>
                  <option value="Ongoing" <?php if(strcasecmp($api->clean($row['status']), 'Ongoing') == 0){ echo 'selected'; } ?>>Ongoing</option>
                  <option value="Finished" <?php if(strcasecmp($api->clean($row['status']), 'Finished') == 0){ echo 'selected'; } ?>>Finished</option>
                </select>
              </td>
            </tr>
            <?php } ?>
            <?php
              }

              $api->free_result($get_workorders);
              $api->close($get_workorders);
            ?>
          </tbody>
        </table>
      </form>
      <h1 class="display-6">Finished Workorders</h1>
      <?php
        try {
          $get_workorders_finished = $api->prepare("SELECT * FROM workorder WHERE status=?");
          if($get_workorders===false){
            throw new Exception("prepare() error: The statement could not be prepared.");
          }

          $mysqli_checks = $api->bind_params($get_workorders_finished, "s", "Finished");
          if($mysqli_checks===false){
            throw new Exception('Execute error: The prepared statement could not be executed.');
          }
      
          $mysqli_checks = $api->execute($get_workorders_finished);
          if($mysqli_checks===false){
            throw new Exception('Execute error: The prepared statement could not be executed.');
          }
      
          $res = $api->get_result($get_workorders_finished);
          if($res===false){
            throw new Exception('get_result() error: Getting result set from statement failed.');
          }
        } catch (Exception $e){
          exit;
          echo $e->getMessage();
        }
      ?>
      <form method="POST" action="./queries.php">
        <button type="submit" class="btn btn-outline-danger" name="delete_workorder">Delete</button>
        <table class="table w-100">
          <thead class="align-middle" style="height: 4em;">
            <tr>
              <th scope="col"></th>
              <th scope="col">order_id</th>
              <th scope="col">client_id</th>
              <th scope="col">order_date</th>
              <th scope="col">amount_due</th>
              <th scope="col">incentive</th>
              <th scope="col">status</th>
            </tr>
          </thead>
          <tbody>
            <?php
              if($api->num_rows($res) > 0){
                while($row = $api->fetch_assoc($res)){
            ?>
            <tr class="align-middle" style="height: 5em;">
              <td scope="row"><input type="checkbox" class="workorder form-check-input" name="item[]" value="<?php echo $row['order_id']?>"/></td>
              <th><?php echo $api->clean($row['order_id']) ?></th>
              <td><?php echo $api->clean($row['client_id']) ?></td>
              <td><?php echo $row['order_date'] ?></td>
              <td><?php echo $row['amount_due'] ?></td>
              <td><?php echo $row['amount_due'] ?></td>
              <td><?php echo $row['incentive'] ?></td>
              <td><?php echo $row['status'] ?></td>
            </tr>
            <?php } ?>
            <?php
              }

              $api->free_result($get_workorders_finished);
              $api->close($get_workorders_finished);
            ?>
          </tbody>
        </table>
      </form>
    </div>
    <div class="tab-pane fade show active" id="clients" role="tabpanel" aria-labelledby="clients-tab">
      <?php
        try {
          $get_clients = $api->prepare("SELECT * FROM client");
          if($get_clients===false){
            throw new Exception("prepare() error: The statement could not be prepared.");
          }
      
          $mysqli_checks = $api->execute($get_clients);
          if($mysqli_checks===false){
            throw new Exception('Execute error: The prepared statement could not be executed.');
          }
      
          $res = $api->get_result($get_clients);
          if($res===false){
            throw new Exception('get_result() error: Getting result set from statement failed.');
          }
        } catch (Exception $e){
          exit;
          echo $e->getMessage();
        }
      ?>
      <form method="POST" action="./queries.php">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="change_client_rows" />
          <label class="form-check-label" for="change_client_rows" id="client_editing_label">Edit</label>
        </div>
        <button type="button" id="client_form select-all" class="btn btn-link">Select All</button>
        <button type="submit" class="btn btn-outline-primary" name="update_client">Update</button>
        <button type="submit" class="btn btn-outline-danger" name="delete_client">Delete</button>
        <table class="table w-100">
          <thead class="align-middle" style="height: 4em;">
            <tr>
              <th scope="col"></th>
              <th scope="col">client_id</th>
              <th scope="col">client_fname</th>
              <th scope="col">client_mi</th>
              <th scope="col">client_lname</th>
              <th scope="col">age</th>
              <th scope="col">home_address</th>
              <th scope="col">contact_number</th>
            </tr>
          </thead>
          <tbody>
            <?php
              if($api->num_rows($res) > 0){
                while($row = $api->fetch_assoc($res)){
            ?>
            <tr class="align-middle" style="height: 5em;">
              <td scope="row"><input type="checkbox" class="client form-check-input" name="item[]" value="<?php echo $row['client_id']?>"/></td>
              <th><input type="text" readonly class="form-control-plaintext" name="client_id[]" value="<?php echo $row['client_id']?>"/></th>
              <td><input type="text" readonly class="client form-control-plaintext" name="client_fname[]" value="<?php echo $api->clean($row['client_fname']) ?>" minlength="2" required/></td>
              <td><input type="text" readonly class="client form-control-plaintext" name="client_mi[]" value="<?php echo $api->clean($row['client_mi']) ?>" minlength="1" maxlength="1" /></td>
              <td><input type="text" readonly class="client form-control-plaintext" name="client_lname[]" value="<?php echo $api->clean($row['client_lname']) ?>" minlength="2" required/></td>
              <td><input type="text" inputmode="numeric" readonly class="client form-control-plaintext" name="age[]" min="1" value="<?php echo $row['age'] ?>" minlength="1" maxlength="3"/></td>
              <td><input type="text" readonly class="client form-control-plaintext" name="home_address[]" value="<?php echo $api->clean($row['home_address']) ?>"/></td>
              <td><input type="text" inputmode="numeric" readonly class="client form-control-plaintext" name="contact_number[]" value="<?php echo $api->clean($row['contact_number']) ?>" minlength="11" maxlength="11"/></td>
            </tr>
            <?php } ?>
            <?php } else { ?>
              <tfoot>
                <td class="p-5"><h1 class="m-3 display-4 fst-italic text-muted">No entries in the client table.</h1></td>
              </tfoot>
            <?php
              }

              $api->free_result($get_clients);
              $api->close($get_clients);
            ?>
          </tbody>
        </table>
      </form>
    </div>
    <div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">...</div>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="script.js"></script>
</html>