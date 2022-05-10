<?php
  session_name("sess_id");
  session_start();
  if(!isset($_SESSION['user']['user_id'])){
    Header("Location: ./index.php");
    die();
  } else {
    require_once '../api/api.php';
    $api = new api();
  }

  try {
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
  <?php require_once '../common/meta.php'; ?>
  <!-- native style -->
  <link href="../style/style.css" rel="stylesheet" scoped>
  <style scoped>
    .Profile, .Profile__sidebar {
      display: flex;
      justify-content: flex-start;
    }

    .Profile {
      flex-direction: row;
      align-items: flex-start;
    }

    .Profile__sidebar {
      flex-direction: column;
      align-items: center;
    }

    #profile_picture {
      height: 275px;
      width: 275px;
    }

    .tabs {
      width: 225px;
    }

    .list-group-item {
      padding: 0.5rem !important;
    }

    .list-group-item button {
      width: 100% !important;
      color: #000000 !important;
      text-decoration: none !important;
      display: flex !important;
      flex-flow: row nowrap;
      justify-content: start;
      align-items: center;
      transition: all .3s;
    }

    .list-group-item button:hover{
      color: #fff !important;
    }

    .list-group-item:hover {
      background: #000000 !important;
    }

    .Profile__form {
      padding: 0 0 0 4rem;
    }

    .Profile__header--account-details, .Profile__header--change-password {
      font-weight: 700;
    }

    .mi {
      display: block;
    }

    .mi-shortened {
      display: none;
    }

    @media (max-width: 1296px) {
      .Profile {
        flex-direction: column !important;
        justify-content: center !important;
      }

      .Profile__sidebar {
        width: 100% !important;
        justify-content: center !important;
      }

      .list-group {
        width: 100% !important;
      }

      .list-group-item {
        width: 100% !important;
        padding: 0 !important;
      }

      .Profile__form {
        width: 100 !important;
        margin: 3rem 0 !important;
        padding: 0 !important;
      }

      .mi {
        display: none !important;
      }

      .mi-shortened {
        display: block !important;
      }
    }
  </style>
  <title>User Profile | NJC Tattoo</title>
</head>
<body>
  <?php require_once '../common/header.php'; ?>
  <div class="content w-60">
    <div class="Profile">
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
      <div class="Profile__form">
        <div id="Profile__account-details" class="d-block">
          <h1 class="Profile__header--account-details">Account Details</h1>
          <form action="../scripts/php/queries.php" method="post" enctype="multipart/form-data">
            <div class="my-4 row align-items-end">
              <div class="col ms-0">
                <label for="first_name" class="form-label fs-5 fw-semibold">First Name</label>
                <input type="text" class="form-control" name="first_name" id="first_name" value="<?php echo $first_name; ?>" minlength="2" maxlength="50" placeholder="First Name" required/>
                <label class="<?php echo isset($_SESSION['first_name_err']) ? "d-flex": "d-none"; ?> text-danger my-2"><span class="material-icons-outlined me-1">info</span><?php if(isset($_SESSION['first_name_err'])) { echo $_SESSION['first_name_err']; } ?></label>
              </div>
              <div class="col-4 me-0">
                <label for="mi" class="form-label fs-5 fw-semibold"><span class="mi">Middle Initial (Optional)</span><span class="mi-shortened">M.I.</span></label>
                <input type="text" class="form-control" name="mi" id="mi" value="<?php echo $mi; ?>" minlength="1" maxlength="2" placeholder="Middle Initial"/>
                <label class="<?php echo isset($_SESSION['mi_err']) ? "d-flex": "d-none"; ?> text-danger my-2"><span class="material-icons-outlined me-1">info</span><?php if(isset($_SESSION['mi_err'])) { echo $_SESSION['mi_err']; } ?></label>
              </div>
            </div>
            <div class="my-4">
              <label for="last_name" class="form-label fs-5 fw-semibold">Last Name</label>
              <input type="text" class="form-control" name="last_name" id="last_name" value="<?php echo $last_name; ?>" minlength="2" maxlength="50" placeholder="Last Name" required/>
              <label class="<?php echo isset($_SESSION['last_name_err']) ? "d-flex": "d-none"; ?> text-danger my-2"><span class="material-icons-outlined me-1">info</span><?php if(isset($_SESSION['last_name_err'])) { echo $_SESSION['last_name_err']; } ?></label>
            </div>
            <div class="my-4">
              <label for="address" class="form-label fs-5 fw-semibold">Home Address</label>
              <input type="text" class="form-control" name="address" id="address" value="<?php echo $address; ?>" minlength="2" maxlength="50" placeholder="Address" required/>
              <label class="<?php echo isset($_SESSION['address_err']) ? "d-flex": "d-none"; ?> text-danger my-2"><span class="material-icons-outlined me-1">info</span><?php if(isset($_SESSION['address_err'])) { echo $_SESSION['address_err']; } ?></label>
            </div>
            <div class="my-4">
              <label for="contact_number" class="form-label fs-5 fw-semibold">Contact Number</label>
              <input type="number" class="form-control" name="contact_number" id="contact_number" <?php if(!empty($contact_number)) { ?>value="<?php echo $contact_number; ?>"<?php } ?> placeholder="Contact Number" required/>
              <label class="<?php echo isset($_SESSION['contact_number_err']) ? "d-flex": "d-none"; ?> text-danger my-2"><span class="material-icons-outlined me-1">info</span><?php if(isset($_SESSION['contact_number_err'])) { echo $_SESSION['contact_number_err']; } ?></label>
            </div>
            <div class="my-4">
              <label for="email" class="form-label fs-5 fw-semibold">Email</label>
              <input type="email" class="form-control" name="email" id="email" value="<?php echo $email; ?>" minlength="2" maxlength="62" placeholder="Email" required/>
              <label class="<?php echo isset($_SESSION['email_err']) ? "d-flex": "d-none"; ?> text-danger my-2"><span class="material-icons-outlined me-1">info</span><?php if(isset($_SESSION['email_err'])) { echo $_SESSION['email_err']; } ?></label>
            </div>
            <div class="my-4">
              <label for="birthdate" class="form-label fs-5 fw-semibold">Birthdate</label>
              <input type="date" class="form-control" name="birthdate" id="birthdate" value="<?php echo date('Y-m-d', strtotime($birthdate)); ?>" />
              <label class="<?php echo isset($_SESSION['birthdate_err']) ? "d-flex": "d-none"; ?> text-danger my-2"><span class="material-icons-outlined me-1">info</span><?php if(isset($_SESSION['birthdate_err'])) { echo $_SESSION['birthdate_err']; } ?></label>
            </div>
            <div class="my-4">
              <label for="image" class="form-label fs-5 fw-semibold">Profile Picture</label>
              <input type="file" class="form-control" accept="image/*" name="image" id="image" onchange="loadFile(event)"/>
              <label class="<?php echo isset($_SESSION['image_err']) ? "d-flex": "d-none"; ?> text-danger my-2"><span class="material-icons-outlined me-1">info</span><?php if(isset($_SESSION['image_err'])) { echo $_SESSION['image_err']; } ?></label>
            </div>
            <input type="hidden" class="d-none" name="client_id" value="<?php echo $client_id; ?>" />
            <input type="hidden" class="d-none" name="user_id" value="<?php echo $user_id; ?>" />
            <button type="submit" class="btn btn-outline-dark" name="update_profile" id="update_profile">Update Profile</button>
            <?php if(isset($_SESSION['res'])){ ?>
              <div class="text-danger my-3">An error occured with the server. Please try again later.</div>
            <?php } ?>
          </form>
        </div>
        <div id="Profile__change-password" class="d-none">
          <h1 class="Profile__header--change-password">Change Password</h1>
          <form action="../scripts/php/queries.php" method="post"> 
            <div class="my-4">
              <label for="password" class="form-label fs-5 fw-semibold">New Password</label>
              <input type="password" class="form-control" name="new_password" id="new_password" minlength="2" placeholder="New Password" />
              <label class="<?php echo isset($_SESSION['new_password_err']) ? "d-flex": "d-none"; ?> text-danger my-2"><span class="material-icons-outlined me-1">info</span><?php if(isset($_SESSION['new_password_err'])) { echo $_SESSION['new_password_err']; } ?></label>
            </div>
            <div class="my-4">
              <label for="confirm_password" class="form-label fs-5 fw-semibold">Confirm New Password</label>
              <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Re-enter Password" />
              <label class="<?php echo isset($_SESSION['confirm_password_err']) ? "d-flex": "d-none"; ?> text-danger my-2"><span class="material-icons-outlined me-1">info</span><?php if(isset($_SESSION['confirm_password_err'])) { echo $_SESSION['confirm_password_err']; } ?></label>
            </div>
            <div class="my-4">
              <label for="password" class="form-label">Please enter your old password below to confirm this action.</label>
              <input type="password" class="form-control" name="password" id="password" placeholder="Password" />
              <label class="<?php echo isset($_SESSION['password_err']) ? "d-flex": "d-none"; ?> text-danger my-2"><span class="material-icons-outlined me-1">info</span><?php if(isset($_SESSION['password_err'])) { echo $_SESSION['password_err']; } ?></label>
            </div>
            <input type="hidden" class="d-none" name="client_id" value="<?php echo $client_id; ?>" />
            <input type="hidden" class="d-none" name="user_id" value="<?php echo $user_id; ?>" />
            <button type="submit" class="btn btn-outline-dark" name="update_password" id="update_password">Update Password</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
<?php echo "<script>var loadFile = function(event){ var image = document.getElementById('image'); var preview = document.getElementById('profile_picture'); if(image.value.length != 0){ preview.style.backgroundImage = 'url(' + URL.createObjectURL(event.target.files[0]) + ')'; preview.onload = () => { URL.revokeObjectURL(preview.style.backgroundImage); }} else { preview.style.backgroundImage = 'url(". $_SESSION['user']['user_avatar'] .")'; }};</script>"; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script>
  // error reporting
  var errors = [];
  
  // tabs
  var tab__account_details = document.getElementById('tabs--account-details');
  var tab__change_password = document.getElementById('tabs--change-password');

  // profile sections
  var profile__account_details = document.getElementById('Profile__account-details');
  var profile__change_password = document.getElementById('Profile__change-password');

  // account details input fields
  var first_name = document.getElementById('first_name');
  var mi = document.getElementById('mi');
  var last_name = document.getElementById('last_name');
  var address = document.getElementById('address');
  var contact_number = document.getElementById('contact_number');
  var email = document.getElementById('email');
  var birthdate = document.getElementById('birthdate');
  var image = document.getElementById('image');

  // change password input fields
  var password = document.getElementById('password');
  var new_password = document.getElementById('new_password');
  var confirm_password = document.getElementById('confirm_password');

  // account details tab
  tab__account_details.addEventListener('click', function(){
    profile__account_details.classList.replace('d-none', 'd-block');
    profile__change_password.classList.replace('d-block', 'd-none');

    first_name.required = true;
    last_name.required = true;
    address.required = true;
    contact_number.required = true;
    email.required = true;
    birthdate.required = true;

    password.required = false;
    new_password.required = false;
    confirm_password.required = false;
  });

  // change password tab
  tab__change_password.addEventListener('click', function(){
    profile__account_details.classList.replace('d-block', 'd-none');
    profile__change_password.classList.replace('d-none', 'd-block');

    first_name.required = false;
    last_name.required = false;
    address.required = false;
    contact_number.required = false;
    email.required = false;
    birthdate.required = false;

    password.required = true;
    new_password.required = true;
    confirm_password.required = true;
  });
</script>
</html>
<?php
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