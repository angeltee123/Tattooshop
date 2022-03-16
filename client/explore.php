<?php
  session_name("sess_id");
  session_start();
  if(!isset($_SESSION['user_id'])){
    Header("Location: ./index.php");
    die();
  } else {
    require_once '../api/api.php';
    $api = new api();
  }

  if(!isset($_SESSION['order_id']) || empty($_SESSION['order_id'])){
    try {
      $client_id = $_SESSION['client_id'];
      $mysqli_checks = $api->get_workorder($client_id);
      if($mysqli_checks!==true){
        throw new Exception('Error: Retrieving client workorder failed.');
      }
    } catch (Exception $e) {
      exit();
      $_SESSION['res'] = $e->getMessage();
      Header("Location: ./index.php");
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require_once '../common/meta.php'; ?>
  <!-- native style -->
  <link href="../style/catalogue.css" rel="stylesheet" scoped>
  <link href="./style/explore.css" rel="stylesheet" scoped>
  <title>Explore | NJC Tattoo</title>
</head>
<body class="w-100">
  <header class="header">
    <nav class="nav-bar row mx-0">
      <ul class="col my-0" id="nav-links">
        <li class="active"><a href="./explore.php">Explore</a></li>
        <li><a href="orders.php">Orders</a></li>
        <li><a href="reservations.php">Bookings</a></li>
      </ul>
      <div class="col d-flex align-items-center justify-content-end my-0 mx-5">
        <div class="btn-group" id="nav-user">
          <button type="button" class="btn p-0" data-bs-toggle="dropdown" aria-expanded="false"><span class="material-icons lh-base display-5">account_circle</span></button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="user.php">Profile</a></li>
              <li>
                <form action="../scripts/php/queries.php" method="post">
                  <button type="submit" class="dropdown-item btn-link" name="logout">Sign Out</button>
                </form>
              </li>
            </ul>
        </div>
      </div>
    </nav>
  </header>
  <div class="content w-70">
    <div class="text-center mb-8">
      <h2 class="fw-bold display-3">Explore</h2>
      <p class="fs-5 text-muted">Find your next tattoo here.</p>
    </div>
    <div class="mb-4 position-relative d-flex flex-row justify-content-start align-items-center">
      <div class="material-icons position-absolute fs-2 ms-4 no-select">search</div>
      <input type="text" class="form-control form-control-lg rounded-pill" style="padding-top: 1.25rem; padding-left: 4.5rem; padding-bottom: 1.25rem; padding-right: 1.25rem;" id="search" placeholder="Search">
    </div>
    <div class="w-100 d-flex flex-row flex-wrap justify-content-between align-items-start" id="catalogue">
      <?php
        $query = $api->select();
        $query = $api->params($query, "*");
        $query = $api->from($query);
        $query = $api->table($query, "tattoo");
        $query = $api->order($query, "cataloged", "DESC");

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
        } catch (Exception $e) {
          exit;
          Header("Location: ./index.php");
          echo $e->getMessage();
        }

        if($api->num_rows($res) > 0){
          while($row = $api->fetch_assoc($res)){
            $id = $api->sanitize_data($row['tattoo_id'], 'string');
            $name = $api->sanitize_data($row['tattoo_name'], 'string');
            $price = number_format($api->sanitize_data($row['tattoo_price'], "float"), 2, '.', '');
            $height = $api->sanitize_data($row['tattoo_height'], 'int');
            $width = $api->sanitize_data($row['tattoo_width'], 'int');
            $image = $api->sanitize_data($row['tattoo_image'], 'string');
            $description = $api->sanitize_data($row['tattoo_description'], 'string');
            $color_scheme = $api->sanitize_data($row['color_scheme'], 'string');
            $complexity = $api->sanitize_data($row['complexity_level'], 'string');  
      ?>
        <a class="tattoo-card my-3 d-block shadow-sm rounded" href="#<?php echo $name?>" style="background-image: url(<?php echo $image; ?>)"></a>
        <div id="<?php echo $name?>" class="tattoo_detail row justify-content-center align-items-start h-100 w-100">
          <div class="order-first tattoo-image col-5 bg-light" style="background-image: url(<?php echo $image; ?>)"></div>
          <div class="order-last d-flex col vh-100 border-start border-1 justify-content-center align-items-center">
            <div class="flex-grow-1">
            <div class="position-absolute top-0 start-0 mt-5 ms-5 d-flex align-items-center justify-content-center bg-white border" style="width: 75px; height: 75px;">
              <a href="./explore.php" class="stretched-link"><span class="material-icons md-48 display-5" style="width: 24px;">arrow_back_ios</span></a>
            </div>
              <div class="w-60 ms-9">
                <form action="../scripts/php/queries.php" method="POST">
                  <input type="hidden" class="d-none" name="tattoo_id" value="<?php echo $id ?>" required />
                  <input type="hidden" class="d-none" name="tattoo_name" value="<?php echo $id ?>" required />
                  <div>
                    <h1 class="display-4 fw-bold my-0"><?php echo $name ?></h1>
                    <h4 class="text-secondary">₱<?php echo $price ?></h4>
                  </div>
                  <div class="my-5 tattoo-description">
                    <p class="text-wrap"><?php echo $description ?></p>
                  </div>
                  <div class="my-3 row">
                    <div class="tattoo-color col">
                      <h4>Color</h4>
                      <div class="d-flex flex-row align-items-center">
                        <?php if(strcasecmp($color_scheme, "Monochrome") == 0){ ?>
                          <div class="color border border-4 rounded-pill" style="background-color: #000000"></div>
                        <?php } else { ?>
                          <div class="color border border-4 rounded-pill" style="background-image: linear-gradient(to bottom right, #FF00FF, blue)"></div>
                        <?php } ?>
                        <p class="color-tooltip d-inline p-2 my-0 ms-2 w-auto bg-dark rounded"><?php if(strcasecmp($color_scheme, "Monochrome") == 0){ echo "Monochrome"; } else { echo "Multicolor"; } ?></p>
                      </div>
                    </div>
                    <div class="tattoo-complexity col-md-8">
                      <h4>Complexity</h4>
                      <p><?php echo $complexity ?></p>
                    </div>
                  </div>
                  <div class="my-5 row">
                    <div class="col">
                      <div class="form-floating">
                        <input type="number" class="form-control" min="1" value="1" placeholder="Quantity" name="quantity" required />
                        <label for="tattoo_width">Order Quantity</label>
                        <p class="my-2 <?php echo isset($_SESSION['quantity_err']) ? "d-block" : "d-none"; ?> text-danger quantity_err"><?php if(isset($_SESSION['quantity_err'])){ echo $_SESSION['quantity_err']; } ?></p>
                      </div>
                    </div>
                    <div class="col">
                      <div class="form-floating">
                        <input type="number" class="form-control" placeholder="Width" min="1" max="24" value="<?php echo $width ?>" name="width" required />
                        <label for="tattoo_width">Width (in inches)</label>
                        <p class="my-2 <?php echo isset($_SESSION['width_err']) ? "d-block" : "d-none"; ?> text-danger width_err"><?php if(isset($_SESSION['width_err'])){ echo $_SESSION['width_err']; } ?></p>
                      </div>
                    </div>
                    <div class="col">
                      <div class="form-floating">
                        <input type="number" class="form-control" placeholder="Height" min="1" max="36" value="<?php echo $height ?>" name="height" required />
                        <label for="tattoo_height">Height (in inches)</label>
                        <p class="my-2 <?php echo isset($_SESSION['height_err']) ? "d-block" : "d-none"; ?> text-danger height_err"><?php if(isset($_SESSION['height_err'])){ echo $_SESSION['height_err']; } ?></p>
                      </div>
                    </div>
                  </div>
                  <p class="my-3 <?php echo isset($_SESSION['res']) ? "d-block" : "d-none"; ?> text-danger"><?php if(isset($_SESSION['res'])){ echo $_SESSION['res']; } ?></p>
                  <button type="submit" class="btn btn-dark btn-lg d-flex align-items-center" name="order_item"><span class="material-icons md-48 lh-base pe-2">add_shopping_cart</span>Add to Order</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php    
          }
        }
      ?>
    </div>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script>
  // search bar
  var search = document.getElementById('search');

  // catalogue
  var catalogue = document.getElementById('catalogue');
  var cards = document.getElementsByClassName('tattoo-card');

  // searching for tattoo
  search.addEventListener('input', function (){
    if(search.value.length == 0){
      catalogue.classList.remove('justify-content-evenly');
      catalogue.classList.add('justify-content-between');

      for(var i = 0, count = cards.length; i < count; i++){
        cards[i].classList.remove('d-none');
        cards[i].classList.add('d-block');
      }
    } else {
      catalogue.classList.remove('justify-content-between');
      catalogue.classList.add('justify-content-evenly');

      for(var i = 0, count = cards.length; i < count; i++){
        item_name = cards[i].href.toLowerCase();
        if(item_name.indexOf(search.value.toLowerCase().replaceAll(' ', '%20')) > -1){
          cards[i].classList.remove('d-none');
          cards[i].classList.add('d-block');
        } else {
          cards[i].classList.remove('d-block');
          cards[i].classList.add('d-none');
        }
      }
    }
  });
</script>
</html>
<?php
  if(isset($_SESSION['res'])){
    unset($_SESSION['res']);
  }
  if(isset($_SESSION['quantity_err'])){
    unset($_SESSION['quantity_err']);
  }
  if(isset($_SESSION['width_err'])){
    unset($_SESSION['width_err']);
  }
  if(isset($_SESSION['height_err'])){
    unset($_SESSION['height_err']);
  }  
?>