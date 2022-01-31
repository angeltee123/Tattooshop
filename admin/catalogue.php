<?php
  session_name("sess_id");
  session_start();
  // if(!isset($_SESSION['user_id']) || strcasecmp($_SESSION['user_type'], "User") == 0){
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
  <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Caslon+Text:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    
    <!-- native style -->
    <link href="../style/bootstrap.css" rel="stylesheet">
    <link href="../style/style.css" rel="stylesheet">
    <link href="./style/catalogue.css" rel="stylesheet" scoped>
    <title>Catalogue | NJC Tattoo</title>
</head>
<body class="w-100">
  <header class="header border-bottom border-2">
    <nav class="nav-bar row mx-0">
      <ul class="col my-0" id="nav-links">
        <li class="active"><a href="catalogue.php">Catalogue</a></li>
        <li><a href="orders.php">Orders</a></li>
        <li><a href="reservations.php">Bookings</a></li>
      </ul>
      <div class="col d-flex align-items-center justify-content-end my-0 mx-5">
        <div class="btn-group" id="nav-user">
          <button type="button" class="btn p-0" data-bs-toggle="dropdown" aria-expanded="false"><span class="material-icons lh-base display-5">account_circle</span></button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="user.php">Profile</a></li>
              <li>
                <form action="../api/queries.php" method="post">
                  <button type="submit" class="dropdown-item btn-link" name="logout">Sign Out</button>
                </form>
              </li>
            </ul>
        </div>
      </div>
    </nav>
  </header>
  <div class="content mx-auto my-5" style="width: 68% !important">
    <h2 class="fw-bold display-6 mb-4">Tattoo Catalogue</h2>
    <div class="w-100 d-flex flex-row flex-wrap justify-content-between align-items-start">
      <?php
        $query = $api->select();
        $query = $api->params($query, "*");
        $query = $api->from($query);
        $query = $api->table($query, "tattoo");
        $query = $api->order($query, "cataloged", "ASC");

        try {
          $statement = $api->prepare($query);
          if($statement===false){
            throw new Exception("prepare() error: The statement could not be prepared.");
          }
      
          $mysqli_checks = $api->execute($statement);
          if($mysqli_checks===false){
            throw new Exception('Execute error: The prepared statement could not be executed.');
          }
      
          $res = $api->get_result($statement);
          if($res===false){
            throw new Exception('get_result() error: Getting result set from statement failed.');
          }
        } catch (Exception $e){
          exit;
          Header("Location: ./index.php");
          echo $e->getMessage();
        }

        $count = 0;

        if($api->num_rows($res)){
          while($row = $api->fetch_assoc($res)){
            $id = $api->clean($row['tattoo_id']);
            $name = $api->clean($row['tattoo_name']);
            $price = $row['tattoo_price'];
            $height = $row['tattoo_height'];
            $width = $row['tattoo_width'];
            $image = $row['tattoo_image'];
            $description = $api->clean($row['tattoo_description']);
            $color_scheme = $api->clean($row['color_scheme']);
            $complexity = $api->clean($row['complexity_level']);  
      ?>
        <a class="order-<?php echo $count++?> tattoo-card my-4 d-block border-secondary border-2 rounded" href="#<?php echo $name?>" style="background-image: url(<?php echo $api->clean($image); ?>)"></a>
        <!-- deletion-modal -->
        <div class="modal fade" id="delete_<?php echo $id ?>" tabindex="-1" aria-labelledby="delete_<?php echo $name ?>_label" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="#delete_<?php echo $name ?>_label">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <p>Are you sure you want to delete this tattoo from the catalogue?</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="./queries.php" method="POST">
                  <input type="hidden" class="d-none" name="tattoo_id" value="<?php echo $id ?>">
                  <button type="submit" class="btn btn-outline-danger" name="delete_tattoo">Yes, delete it</button>
                </form>
              </div>
            </div>
          </div>
        </div>
        <div id="<?php echo $name?>" class="tattoo_detail align-items-center justify-content-center">
          <form action="./queries.php" method="POST" class="cont row">
            <div class="order-first col-5 h-100 tattoo-image" style="background-image: url(<?php echo $api->clean($image); ?>)"></div>
            <div class="order-last col p-5 h-100 d-flex flex-column position-relative">
              <div class="position-absolute end-0 me-5">
                <a href="./catalogue.php" class="float-end"><span class="material-icons md-48">close</span></a>
              </div>
              <div class="mt-5 mb-3">
                <input type="text" class="form-control form-control-lg my-3 fs-display-5 fw-bold" value="<?php echo $name ?>" name="tattoo_name"></input>
                <input type="hidden" class="d-none" name="tattoo_id" value="<?php echo $id ?>">
                <div class="form-floating ">
                  <input type="text" class="form-control" value="<?php echo $price ?>" min="1" name="tattoo_price"></input>
                  <label for="tattoo_price">Price (Php)</label>
                </div>
              </div>
              <div>
                <h5 class="my-3">Description</h5>
                <textarea class="form-control p-3 text-wrap" name="tattoo_description" rows="5"><?php echo $description ?></textarea>
              </div>
              <div class="mt-4 mb-3 row">
                <div class="col">
                  <h5 class="my-2">Color</h5>
                  <select name="color_scheme" class="form-select">
                    <option value="Monochrome" <?php if(strcasecmp($color_scheme, 'Monochrome') == 0){ echo "selected"; } ?>>Monochrome</option>
                    <option value="Multicolor" <?php if(strcasecmp($color_scheme, 'Multicolor') == 0){ echo "selected"; } ?>>Multicolor</option>
                  </select>
                </div>
                <div class="col">
                  <h5 class="my-2">Complexity</h5>
                  <select name="complexity_level" class="form-select">
                    <option value="Simple" <?php if(strcasecmp($complexity, 'Simple') == 0){ echo "selected"; } ?>>Simple</option>
                    <option value="Complex" <?php if(strcasecmp($complexity, 'Complex') == 0){ echo "selected"; } ?>>Complex</option>
                  </select>
                </div>
              </div>
              <div class="my-3 row">
                <h5 class="my-2">Dimensions</h5>
                <div class="col">
                  <div class="form-floating">
                    <input type="number" class="form-control" id="<?php echo $name ?>_width" value="<?php echo $width ?>" placeholder="Width" min="1" max="24" name="tattoo_width" required>
                    <label for="<?php echo $name ?>_width">Width (in inches)</label>
                  </div>
                </div>
                <div class="col">
                  <div class="form-floating">
                    <input type="number" class="form-control" id="<?php echo $name ?>_height" value="<?php echo $height ?>" placeholder="Height" min="1" max="36" name="tattoo_height" required>
                    <label for="<?php echo $name ?>_height">Height (in inches)</label>
                  </div>
                </div>
              </div>
              <div class="row mt-4">
                <div class="col">
                  <button type="submit" class="btn btn-primary btn-lg" name="update_tattoo">Save Changes</button>
                  <button type="button" class="btn btn-outline-danger btn-lg" data-bs-toggle="modal" data-bs-target="#delete_<?php echo $id ?>">Delete</button>
                </div>
              </div> 
            </div>
          </form>
        </div>
      <?php    
          }
        }
      ?>
    </div>
  </div>
</body>
<script src="../api/bootstrap-bundle-min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> -->
</html>
<?php
  if(isset($_SESSION['width_err'])){
    unset($_SESSION['width_err']);
  }
  if(isset($_SESSION['height_err'])){
    unset($_SESSION['height_err']);
  }
  if(isset($_SESSION['quantity_err'])){
    unset($_SESSION['quantity_err']);
  }
  if(isset($_SESSION['res'])){
    unset($_SESSION['res']);
  }
?>