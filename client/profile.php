<?php
  session_name("sess_id");
  session_start();

  // navigation guard
  if(!isset($_SESSION['user']['user_id'])){
    Header("Location: ../admin/index.php");
    die();
  } else {
    require_once '../api/api.php';
    $api = new api();
  }

  try {
    // retrieve client account data
    $client_id = $api->sanitize_data($_SESSION['user']['client_id'], 'string');

    $statement = $api->prepare("SELECT user_email, user_password, client_fname, client_mi, client_lname, home_address, contact_number, birthdate FROM (client INNER JOIN user ON client.client_id=user.client_id) WHERE user.client_id=? LIMIT 1");
    if($statement===false){
      throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
    }

    $mysqli_checks = $api->bind_params($statement, "s", $client_id);
    if($mysqli_checks===false){
      throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
    }

    $mysqli_checks = $api->execute($statement);
    if($mysqli_checks===false){
      throw new Exception('Execute error: The prepared statement could not be executed.');
    }

    $user_data = $api->get_result($statement);
    if($user_data===false){
      throw new Exception('get_result() error: Getting result set from statement failed.');
    }

    if($api->num_rows($user_data) > 0){
      $profile = $api->fetch_assoc($user_data);
      $user_id = $_SESSION['user']['user_id'];
      $client_id = $_SESSION['user']['client_id'];
      $email = $api->sanitize_data($profile['user_email'], 'string');
      $password = trim($profile['user_password']);
      $first_name = $api->sanitize_data($profile['client_fname'], 'string');
      $mi = $api->sanitize_data($profile['client_mi'], 'string');
      $last_name = $api->sanitize_data($profile['client_lname'], 'string');
      $address = $api->sanitize_data($profile['home_address'], 'string');
      $contact_number = $api->sanitize_data($profile['contact_number'], 'int');
      $birthdate = $api->sanitize_data($profile['birthdate'], 'string');
    } else {
      throw new Exception('Error: Retrieving user data failed.');
    }

    $api->free_result($statement);
    $mysqli_checks = $api->close($statement);
    if($mysqli_checks===false){
      throw new Exception('The prepared statement could not be closed.');
    }
  } catch (Exception $e) { 
    $_SESSION['res'] = $e->getMessage();
    Header("Location: ../client/index.php");
    exit();
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <!-- meta -->
  <?php require_once '../common/meta.php'; ?>

  <!-- native style -->
  <link href="./style/profile.css" rel="stylesheet" scoped>
  <title>User Profile | NJC Tattoo</title>
</head>
<body>
  <!-- navigation bar -->
  <?php require_once '../common/header.php'; ?>
  
  <!-- page content -->
  <div class="content w-60">
    <div class="Profile">
      <!-- page controls -->
      <div class="Profile__sidebar">
        <div class="avatar border border-3" id="profile_picture" style="background-image: url(<?php echo $_SESSION['user']['user_avatar']; ?>)"></div>
        <ul class="tabs list-group pt-5">
          <li class="list-group-item"><button type="button" class="btn btn-link stretched-link" id="tabs--account-details"><span class="material-icons me-1">portrait</span>Account Details</button></li>
          <li class="list-group-item"><button type="button" class="btn btn-link stretched-link" id="tabs--change-password"><span class="material-icons me-1">vpn_key</span>Change Password</button></li>
          <li class="list-group-item">
            <form action="../scripts/php/queries.php" method="post">
              <button type="submit" class="btn btn-link stretched-link" name="logout"><span class="material-icons me-1">logout</span>Sign Out</button>
            </form>
          </li>
        </ul>
      </div>

      <!-- profile form -->
      <div class="Profile__form">
        <!-- account details -->
        <div id="Profile__account-details" class="d-block">
          <h1 class="Profile__header--account-details" style="font-family: 'Yeseva One', cursive, serif;">Account Details</h1>
          <form id="Profile__account-details-form" action="../scripts/php/queries.php" method="post" enctype="multipart/form-data">
            <div class="my-4 row align-items-start w">
              <!-- first name -->
              <div class="col ms-0">
                <label for="first_name" class="form-label fs-5 fw-semibold">First Name</label>
                <input type="text" class="form-control" name="first_name" id="first_name" value="<?php echo $first_name; ?>" minlength="2" maxlength="50" placeholder="First Name" required/>
                <label id="first_name_err" class="error-message <?php echo isset($_SESSION['first_name_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['first_name_err'])) { echo $_SESSION['first_name_err']; } ?></span></label>
              </div>

              <!-- middle initial -->
              <div class="col-4 me-0">
                <label for="mi" class="form-label fs-5 fw-semibold"><span>M.I.</span></label>
                <input type="text" class="form-control" name="mi" id="mi" value="<?php echo $mi; ?>" minlength="1" maxlength="2" placeholder="Middle Initial"/>
                <label id="mi_err" class="error-message <?php echo isset($_SESSION['mi_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['mi_err'])) { echo $_SESSION['mi_err']; } ?></span></label>
              </div>
            </div>

            <!-- last name -->
            <div class="my-4">
              <label for="last_name" class="form-label fs-5 fw-semibold">Last Name</label>
              <input type="text" class="form-control" name="last_name" id="last_name" value="<?php echo $last_name; ?>" minlength="2" maxlength="50" placeholder="Last Name" required/>
              <label id="last_name_err" class="error-message <?php echo isset($_SESSION['last_name_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['last_name_err'])) { echo $_SESSION['last_name_err']; } ?></span></label>
            </div>

            <!-- home address -->
            <div class="my-4">
              <label for="address" class="form-label fs-5 fw-semibold">Home Address</label>
              <input type="text" class="form-control" name="address" id="address" value="<?php echo $address; ?>" minlength="2" maxlength="50" placeholder="Address" required/>
              <label id="address_err" class="error-message <?php echo isset($_SESSION['address_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['address_err'])) { echo $_SESSION['address_err']; } ?></span></label>
            </div>

            <!-- contact number -->
            <div class="my-4">
              <label for="contact_number" class="form-label fs-5 fw-semibold">Contact Number</label>
              <input type="number" class="form-control" name="contact_number" id="contact_number" <?php if(!empty($contact_number)) { ?>value="<?php echo $contact_number; ?>"<?php } ?> placeholder="Contact Number" required/>
              <label id="contact_number_err" class="error-message <?php echo isset($_SESSION['contact_number_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['contact_number_err'])) { echo $_SESSION['contact_number_err']; } ?></span></label>
            </div>

            <!-- email address -->
            <div class="my-4">
              <label for="email" class="form-label fs-5 fw-semibold">Email</label>
              <input type="email" class="form-control" name="email" id="email" value="<?php echo $email; ?>" minlength="2" maxlength="62" placeholder="Email" required/>
              <label id="email_err" class="error-message <?php echo isset($_SESSION['email_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['email_err'])) { echo $_SESSION['email_err']; } ?></span></label>
            </div>

            <!-- birthdate -->
            <div class="my-4">
              <label for="birthdate" class="form-label fs-5 fw-semibold">Birthdate</label>
              <input type="date" class="form-control" name="birthdate" id="birthdate" value="<?php echo date('Y-m-d', strtotime($birthdate)); ?>" />
              <label id="birthdate_err" class="error-message <?php echo isset($_SESSION['birthdate_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['birthdate_err'])) { echo $_SESSION['birthdate_err']; } ?></span></label>
            </div>

            <!-- profile avatar -->
            <div class="my-4">
              <label for="image" class="form-label fs-5 fw-semibold">Profile Picture</label>
              <input type="file" class="form-control" accept="image/*" name="image" id="image" onchange="loadFile(event)"/>
              <label class="error-message <?php echo isset($_SESSION['image_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['image_err'])) { echo $_SESSION['image_err']; } ?></span></label>
            </div>

            <input type="hidden" class="d-none" name="client_id" value="<?php echo $client_id; ?>" />
            <input type="hidden" class="d-none" name="user_id" value="<?php echo $user_id; ?>" />

            <!-- update account -->
            <button type="submit" class="btn btn-outline-dark" name="update_profile" id="update_profile">Update Profile</button>
            
            <?php if(isset($_SESSION['res'])){ ?>
              <div class="text-danger my-3">An error occured with the server. Please try again later.</div>
            <?php } ?>
          </form>
        </div>

        <!-- change password -->
        <div id="Profile__change-password" class="d-none">
          <h1 class="Profile__header--change-password" style="font-family: 'Yeseva One', cursive, serif;">Change Password</h1>
          <form id="Profile__change-password-form" action="../scripts/php/queries.php" method="post"> 
            <!-- new password -->
            <div class="my-4">
              <label for="password" class="form-label fs-5 fw-semibold">New Password</label>
              <input type="password" class="form-control" name="new_password" id="new_password" minlength="2" placeholder="New Password" />
              <label id="new_password_err" class="error-message <?php echo isset($_SESSION['new_password_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['new_password_err'])) { echo $_SESSION['new_password_err']; } ?></span></label>
            </div>

            <!-- confirm new password -->
            <div class="my-4">
              <label for="confirm_password" class="form-label fs-5 fw-semibold">Confirm New Password</label>
              <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Re-enter Password" />
              <label id="confirm_password_err" class="error-message <?php echo isset($_SESSION['confirm_password_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['confirm_password_err'])) { echo $_SESSION['confirm_password_err']; } ?></span></label>
            </div>

            <!-- old password -->
            <div class="my-4">
              <label for="password" class="form-label">Please enter your old password below to confirm this action.</label>
              <input type="password" class="form-control" name="password" id="password" placeholder="Password" />
              <label id="password_err" class="error-message <?php echo isset($_SESSION['password_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['password_err'])) { echo $_SESSION['password_err']; } ?></span></label>
            </div>

            <input type="hidden" class="d-none" name="client_id" value="<?php echo $client_id; ?>" />
            <input type="hidden" class="d-none" name="user_id" value="<?php echo $user_id; ?>" />

            <!-- change password -->
            <button type="submit" class="btn btn-outline-dark" name="update_password" id="update_password">Update Password</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
<?php echo "<script>var loadFile = function(event){ var image = document.getElementById('image'); var preview = document.getElementById('profile_picture'); if(image.value.length != 0){ preview.style.backgroundImage = 'url(' + URL.createObjectURL(event.target.files[0]) + ')'; preview.onload = () => { URL.revokeObjectURL(preview.style.backgroundImage); }} else { preview.style.backgroundImage = 'url(". $_SESSION['user']['user_avatar'] .")'; }};</script>"; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script src="../api/api.js"></script>
<script src="../scripts/js/profile.js"></script>
</html>
<?php
  // refresh back-end validation feedback
  if(isset($_SESSION['res'])){
    unset($_SESSION['res']);
  }
  if(isset($_SESSION['first_name_err'])){
    unset($_SESSION['first_name_err']);
  }
  if(isset($_SESSION['last_name_err'])){
    unset($_SESSION['last_name_err']);
  }
  if(isset($_SESSION['mi_err'])){
    unset($_SESSION['mi_err']);
  }
  if(isset($_SESSION['address_err'])){
    unset($_SESSION['address_err']);
  }
  if(isset($_SESSION['contact_number_err'])){
    unset($_SESSION['contact_number_err']);
  }
  if(isset($_SESSION['email_err'])){
    unset($_SESSION['email_err']);
  }
  if(isset($_SESSION['birthdate_err'])){
    unset($_SESSION['birthdate_err']);
  }
  if(isset($_SESSION['image_err'])){
    unset($_SESSION['image_err']);
  }
  if(isset($_SESSION['password_err'])){
    unset($_SESSION['password_err']);
  }
  if(isset($_SESSION['new_password_err'])){
    unset($_SESSION['new_password_err']);
  }
  if(isset($_SESSION['confirm_password_err'])){
    unset($_SESSION['confirm_password_err']);
  }
?>