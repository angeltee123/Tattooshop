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
  <style>
  .content h1 {
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
  <title>Log In | NJC Tattoo</title>
</head>
<body>
<div class="row justify-content-center align-items-start w-100 vh-100">
  <div class="col-5 order-first bg-img"></div>  
  <div class="col order-last d-flex justify-content-center align-items-center">
    <div class="flex-grow-1">
      <div style="margin-left: 7.5rem; width: 48%">
        <h1 class="display-4">NJC Tattoo</h1>
        <form action="../scripts/php/queries.php" method="post">
          <div class="my-4">
            <input type="email" class="form-control py-2 ps-3 border-2 rounded-pill" name="email" id="email" placeholder="Email" required />
          </div>
          <div class="my-4">
            <input type="password" class="form-control py-2 ps-3 border-2 rounded-pill" name="password" id="password" placeholder="Password" required />
          </div>
          <button type="submit" class="btn btn-dark py-2 ps-3 w-100 rounded-pill text-center" name="login">Sign In</button>
          <?php if(!empty($_SESSION['res'])){ ?>
            <div class="text-danger mt-4"><?php echo $_SESSION['res']; ?></div>
          <?php } ?>
        </form>
        <hr class="my-5" />
        <div class="fs-6 my-4 text-center"><span class="text-muted">Don't have an account?</span> <a href="./register.php" class="fw-bold">Sign Up</a></div>
      </div>
    </div>
  </div>
  </div>
</body>
<script>
</script>
</html>
<?php
  if(isset($_SESSION['res'])){
    unset($_SESSION['res']);
  }
?>