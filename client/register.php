<?php include_once '../api/connection.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link href="./style/style.css" rel="stylesheet">
    <title>NJC Tattoo | Sign Up</title>
</head>
<body>
    <h1>Create Account</h1>
    <form action="../api/queries.php" method="post">
        <input type="text" class="form-control form-control-lg rounded-pill" name="first_name" id="first_name" placeholder="First Name" />
        <input type="text" class="form-control form-control-lg rounded-pill" name="last_name" id="last_name" placeholder="Last Name" />
        <input type="email" class="form-control form-control-lg rounded-pill" name="email" id="email" placeholder="Email" />
        <input type="password" class="form-control form-control-lg rounded-pill" name="password" id="password" placeholder="Password" />
        <input type="password" class="form-control form-control-lg rounded-pill" name="confirm_password" id="confirm_password" placeholder="Re-enter Password" />
        <button type="submit" class="btn btn-lg btn-dark rounded-pill" name="signup">Sign Up</button>
    </form>
</body>
</html>