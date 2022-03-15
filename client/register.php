<?php
  session_name("sess_id");
  session_start();
  if(isset($_SESSION['user_id'])){
    Header("Location: ./index.php");
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require_once '../common/meta.php'; ?>  
  <!-- native style -->
  <link href="../style/bootstrap.css" rel="stylesheet">
  <link href="../style/style.css" rel="stylesheet">
  <style>
    .content h1{
      font-family: 'Libre Caslon Text', 'Arial', sans-serif;
      font-weight: bold;
    }

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
  <div class="row justify-content-center align-items-start w-100 vh-100">
    <div class="col-5 order-first bg-img"></div>  
    <div class="col order-last d-flex justify-content-center align-items-center">
      <div class="flex-grow-1">
        <div style="margin-left: 7.5rem; width: 48%">
          <h1 class="display-4">Create Account</h1>
          <form action="../scripts/php/queries.php" method="post">
            <div class="my-4">
              <input type="text" class="form-control form-control py-2 ps-3 border-2 rounded-pill" name="first_name" id="first_name" placeholder="First Name" />
              <?php if(!empty($_SESSION['first_name_err'])){ ?>
                <label class="text-danger ms-3 my-1"><?php echo $_SESSION['first_name_err']; ?></label>
              <?php } ?>
            </div>
            <div class="my-4">
              <input type="text" class="form-control py-2 ps-3 border-2 rounded-pill" name="last_name" id="last_name" placeholder="Last Name" />
              <?php if(!empty($_SESSION['last_name_err'])){ ?>
                <label class="text-danger ms-3 my-1"><?php echo $_SESSION['last_name_err']; ?></label>
              <?php } ?>
            </div>
            <div class="my-4">
              <input type="email" class="form-control py-2 ps-3 border-2 rounded-pill" name="email" id="email" placeholder="Email" />
              <?php if(!empty($_SESSION['email_err'])){ ?>
                <label class="text-danger ms-3 my-1"><?php echo $_SESSION['email_err']; ?></label>
              <?php } ?>
            </div>
            <div class="my-4">
              <input type="password" class="form-control py-2 ps-3 border-2 rounded-pill" name="password" id="password" placeholder="Password" />
              <?php if(!empty($_SESSION['password_err'])){ ?>
                <label class="text-danger ms-3 my-1"><?php echo $_SESSION['password_err']; ?></label>
              <?php } ?>
            </div>
            <div class="my-4">
              <input type="password" class="form-control py-2 ps-3 border-2 rounded-pill" name="confirm_password" id="confirm_password" placeholder="Re-enter Password" />
              <?php if(!empty($_SESSION['confirm_password_err'])){ ?>
                <label class="text-danger ms-3 my-1"><?php echo $_SESSION['confirm_password_err']; ?></label>
              <?php } ?>
            </div>
            <button type="submit" class="btn btn-dark disabled py-2 ps-3 w-100 rounded-pill text-center" name="signup" id="signup">Sign Up Now</button>
            <div class="form-check my-3">
              <input class="form-check-input" type="checkbox" id="tos_agreed">
              <label class="form-check-label" for="tos_agreed" style="font-size: .85em">By clicking "Sign Up Now", I agree to NJC Tattoo's Terms and Conditions.</label>
            </div>
            <?php if(isset($_SESSION['res'])){ ?>
              <div class="text-danger my-3">An error occured with the server. Please try again later.</div>
            <?php } ?>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
<script>
  var checkbox = document.getElementById("tos_agreed");
  var register = document.getElementById("signup");

  checkbox.addEventListener('change', function(){
    if(this.checked){
      register.classList.remove("disabled");
    } else {
      if(!register.classList.contains("disabled")){
        register.classList.add("disabled");
      }
    }
  });
</script>
</html>
<?php
  if(isset($_SESSION['first_name_err'])){
    unset($_SESSION['first_name_err']);
  }
  if(isset($_SESSION['last_name_err'])){
    unset($_SESSION['last_name_err']);
  }
  if(isset($_SESSION['email_err'])){
    unset($_SESSION['email_err']);
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