<?php session_start(); ?>
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
    <link href="./style/style.css" rel="stylesheet">
    <title>Sign Up | NJC Tattoo</title>
</head>
<body>
    <h1>Sign Up</h1>
    <form action="../api/queries.php" method="post">
        <input type="text" class="form-control form-control-lg py-2 ps-4 border-2 rounded-pill" name="first_name" id="first_name" placeholder="First Name" />
        <?php if(isset($_SESSION['first_name_err'])){ ?>
            <span class="invalid-feedback"><?php echo $_SESSION['first_name_err']; ?></span>
        <?php } ?>
        <input type="text" class="form-control form-control-lg py-2 ps-4 border-2 rounded-pill" name="last_name" id="last_name" placeholder="Last Name" />
        <?php if(isset($_SESSION['last_name_err'])){ ?>
            <span class="invalid-feedback"><?php echo $_SESSION['last_name_err']; ?></span>
        <?php } ?>
        <input type="email" class="form-control form-control-lg py-2 ps-4 border-2 rounded-pill" name="email" id="email" placeholder="Email" />
        <?php if(isset($_SESSION['email_err'])){ ?>
            <span class="invalid-feedback"><?php echo $_SESSION['email_err']; ?></span>
        <?php } ?>
        <input type="password" class="form-control form-control-lg py-2 ps-4 border-2 rounded-pill" name="password" id="password" placeholder="Password" />
        <?php if(isset($_SESSION['password_err'])){ ?>
            <span class="invalid-feedback"><?php echo $_SESSION['password_err']; ?></span>
        <?php } ?>
        <input type="password" class="form-control form-control-lg py-2 ps-4 border-2 rounded-pill" name="confirm_password" id="confirm_password" placeholder="Re-enter Password" />
        <?php if(isset($_SESSION['confirm_password_err'])){ ?>
            <span class="invalid-feedback"><?php echo $_SESSION['confirm_password_err']; ?></span>
        <?php } ?>
        <button type="submit" class="btn btn-lg btn-dark rounded-pill" name="signup">Sign Up</button>
    </form>
</body>
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
?>