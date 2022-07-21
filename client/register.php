<?php
  session_name("sess_id");
  session_start();

  // navigation guard
  if(isset($_SESSION['user']['user_id'])){
    Header("Location: ./index.php");
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <!-- meta -->
  <?php require_once '../common/meta.php'; ?>  
  
  <!-- native style -->
  <style scoped>
    .bg-img {
      background-image: url('../images/allef-vinicius-hxNiXP498UI-unsplash.jpg');
      background-position: center;
      background-repeat: no-repeat;
      background-size: cover;
      height: 100%;
    }
    
    .col {
      height: 100%;
    }
  </style>
  <title>Sign Up | NJC Tattoo</title>
</head>
<body>
  <div class="row justify-content-center align-items-start w-100 vh-100 mx-0">
    <!-- page content -->
    <div class="col-5 order-first bg-img"></div>  
    <div class="col order-last d-flex justify-content-center align-items-center">
      <div class="flex-grow-1">
        <div style="margin-left: 7.5rem; width: 48%">
          <h1 class="display-4 fw-bold" style="font-family: 'Yeseva One', cursive, serif;">Create Account</h1>
          
          <!-- page form -->
          <form id="Registration__form" action="../scripts/php/queries.php" method="post">
            <!-- first name -->
            <div class="my-3">
              <input type="text" class="form-control py-2 ps-3 border-2 rounded-pill" name="first_name" id="first_name" minlength="2" maxlength="50" placeholder="First Name" />
              <label id="first_name_err" class="error-message <?php echo isset($_SESSION['first_name_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['first_name_err'])) { echo $_SESSION['first_name_err']; } ?></span></label>
            </div>

            <!-- last name -->
            <div class="my-3">
              <input type="text" class="form-control py-2 ps-3 border-2 rounded-pill" name="last_name" id="last_name" minlength="2" maxlength="50" placeholder="Last Name" />
              <label id="last_name_err" class="error-message <?php echo isset($_SESSION['last_name_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['last_name_err'])) { echo $_SESSION['last_name_err']; } ?></span></label>
            </div>

            <!-- email address -->
            <div class="my-3">
              <input type="email" class="form-control py-2 ps-3 border-2 rounded-pill" name="email" id="email" minlength="2" maxlength="62" placeholder="Email" />
              <label id="email_err" class="error-message <?php echo isset($_SESSION['email_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['email_err'])) { echo $_SESSION['email_err']; } ?></span></label>
            </div>

            <!-- birthdate -->
            <div class="my-3">
              <label for="birthdate" class="form-label text-muted ms-3">Birthdate</label>
              <input type="date" class="form-control py-2 ps-3 border-2 rounded-pill" name="birthdate" id="birthdate" />
              <label id="birthdate_err" class="error-message <?php echo isset($_SESSION['birthdate_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['birthdate_err'])) { echo $_SESSION['birthdate_err']; } ?></span></label>
            </div>

            <!-- password -->
            <div class="my-3">
              <input type="password" class="form-control py-2 ps-3 border-2 rounded-pill" name="password" id="password" minlength="2" placeholder="Password" />
              <label id="password_err" class="error-message <?php echo isset($_SESSION['password_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['password_err'])) { echo $_SESSION['password_err']; } ?></span></label>
            </div>

            <!-- confirm password -->
            <div class="my-3">
              <input type="password" class="form-control py-2 ps-3 border-2 rounded-pill" name="confirm_password" id="confirm_password" placeholder="Re-enter Password" />
              <label id="confirm_password_err" class="error-message <?php echo isset($_SESSION['confirm_password_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['confirm_password_err'])) { echo $_SESSION['confirm_password_err']; } ?></span></label>
            </div>

            <!-- signup -->
            <button type="submit" class="btn btn-dark py-2 ps-3 w-100 rounded-pill text-center" name="signup" id="signup" disabled>Sign Up Now</button>
            
            <!-- tos agree -->
            <div class="form-check my-3">
              <input class="form-check-input" type="checkbox" id="tos_agreed">
              <label class="form-check-label" for="tos_agreed" style="font-size: .85em">By clicking "Sign Up Now", I agree to NJC Tattoo's Terms and Conditions.</label>
            </div>
            
            <label class="error-message <?php echo isset($_SESSION['res']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['res'])) { echo $_SESSION['res']; } ?></span></label>
          </form>

          <!-- page divider -->
          <hr class="my-4" />

          <!-- login page link -->
          <div class="fs-6 my-4 text-center"><span class="text-muted">Already have an account?</span> <a href="./index.php" class="fw-bold">Login</a></div>
        </div>
      </div>
    </div>
  </div>
</body>
<script src="../api/api.js"></script>
<script src="../scripts/js/register.js"></script>
</html>
<?php
  // refresh back-end validation feedback
  if(isset($_SESSION['first_name_err'])){
    unset($_SESSION['first_name_err']);
  }
  if(isset($_SESSION['last_name_err'])){
    unset($_SESSION['last_name_err']);
  }
  if(isset($_SESSION['email_err'])){
    unset($_SESSION['email_err']);
  }
  if(isset($_SESSION['birthdate_err'])){
    unset($_SESSION['birthdate_err']);
  }
  if(isset($_SESSION['password_err'])){
    unset($_SESSION['password_err']);
  }
  if(isset($_SESSION['confirm_password_err'])){
    unset($_SESSION['confirm_password_err']);
  }
  if(isset($_SESSION['res'])){
    unset($_SESSION['res']);
  }
?>