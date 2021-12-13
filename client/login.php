<?php
  session_name("sess_id");
  session_start();
  if(isset($_SESSION['user_id'])){
    Header("Location: index.php");
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
  <link href="https://fonts.googleapis.com/css2?family=Libre+Caslon+Text:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
  
  <!-- bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
  
  <!-- native style -->
  <link href="./style/style.css" rel="stylesheet">
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
  <title>Log In | NJC Tattoo</title>
</head>
<body class="content">
<div class="row justify-content-center align-items-start w-100 vh-100">
  <div class="col-5 order-first bg-img"></div>  
  <div class="col order-last d-flex justify-content-center align-items-center">
    <div class="flex-grow-1">
      <div style="margin-left: 7.5rem; width: 48%">
        <h1 class="display-4">NJC Tattoo</h1>
        <form action="../api/queries.php" method="post">
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