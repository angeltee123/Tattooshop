<?php
  session_name("sess_id");
  session_start();
  if(!isset($_SESSION['user']['user_id']) || strcasecmp($_SESSION['user']['user_type'], "User") != 0){
    Header("Location: ./index.php");
    die();
  } else {
    require_once '../api/api.php';
    $api = new api();

    try {
      $statement = $api->prepare("SELECT * FROM tattoo ORDER BY cataloged DESC");
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

      $api->free_result($statement);
      $mysqli_checks = $api->close($statement);
      if($mysqli_checks===false){
      throw new Exception('The prepared statement could not be closed.');
      }
    } catch (Exception $e) {
      Header("Location: ./index.php");
      echo $e->getMessage();
      exit;
    }
  }

  if(!isset($_SESSION['order']['order_id']) || empty($_SESSION['order']['order_id'])){
    try {
      $client_id = $_SESSION['user']['client_id'];
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
  <link href="../style/catalogue.css" rel="stylesheet" scoped>
  <!-- native style -->
  <style scoped>
    .Catalogue__cards__modal__preview-body__form {
      width: 60%;
    }

    .Catalogue__cards__modal__preview-body__form__color {
      height: 27.5px;
      width: 27.5px;
      border: 4px solid #dee2e6 !important;
      border-radius: 360px !important;
      cursor: pointer;
    }

    .Catalogue__cards__modal__preview-body__form__color--tooltip {
      display: inline !important;
      padding: 0.5rem !important;
      margin: 0 0 0 0.5rem !important;
      font-size: .93em;
      color: #fff;
      background-color: #212529;
      border-radius: .25rem !important;
      opacity: 0;
      transition: all .25s;
    }

    .Catalogue__cards__modal__preview-body__form__color:hover + .Catalogue__cards__modal__preview-body__form__color--tooltip {
      opacity: 1;
    }
  </style>
  <title>Explore | NJC Tattoo</title>
</head>
<body class="w-100">
  <?php require_once '../common/header.php'; ?>
  <div class="Catalogue content">
    <div class="Catalogue__header">
      <h2 class="fw-bold display-3">Explore</h2>
      <p class="fs-5 text-muted">Find your next tattoo here.</p>
    </div>
    <div class="Catalogue__search-bar">
      <div class="material-icons position-absolute fs-2 ms-4 no-select">search</div>
      <input type="text" class="Catalogue__search-bar__input form-control form-control-lg" id="search" placeholder="Search">
    </div>
    <div class="Catalogue__cards justify-content-between" id="Catalogue">
      <?php
        if($api->num_rows($res) > 0){
          while($tattoo = $api->fetch_assoc($res)){
            $id = $api->sanitize_data($tattoo['tattoo_id'], 'string');
            $name = $api->sanitize_data($tattoo['tattoo_name'], 'string');
            $price = number_format($api->sanitize_data($tattoo['tattoo_price'], "float"), 2, '.', '');
            $height = $api->sanitize_data($tattoo['tattoo_height'], 'int');
            $width = $api->sanitize_data($tattoo['tattoo_width'], 'int');
            $image = $api->sanitize_data($tattoo['tattoo_image'], 'string');
            $description = $api->sanitize_data($tattoo['tattoo_description'], 'string');
            $color_scheme = $api->sanitize_data($tattoo['color_scheme'], 'string');
            $complexity = $api->sanitize_data($tattoo['complexity_level'], 'string');  
      ?>
        <a class="Catalogue__cards__card shadow-sm d-block" href="#<?php echo $name?>" style="background-image: url(<?php echo $image; ?>)"></a>
        <div id="<?php echo $name?>" class="Catalogue__cards__modal">
          <div class="Catalogue__cards__modal__preview-image col-5" style="background-image: url(<?php echo $image; ?>)"></div>
          <div class="Catalogue__cards__modal__preview-body col">
            <div class="flex-grow-1">
            <div class="Catalogue__cards__modal--back">
              <a href="./explore.php" class="stretched-link"><span class="material-icons md-48 display-5" style="width: 24px;">arrow_back_ios</span></a>
            </div>
              <div class="Catalogue__cards__modal__preview-body__form">
                <form action="../scripts/php/queries.php" method="POST">
                  <input type="hidden" class="d-none" name="tattoo_id" value="<?php echo $id ?>" required/>
                  <input type="hidden" class="d-none" name="tattoo_name" value="<?php echo $id ?>" required/>
                  <div>
                    <h1 class="display-4 fw-bold my-0"><?php echo $name ?></h1>
                    <h4 class="text-secondary">₱<?php echo $price ?></h4>
                  </div>
                  <div class="my-5 text-justify">
                    <p class="text-wrap"><?php echo $description ?></p>
                  </div>
                  <div class="my-3 row">
                    <div class="col">
                      <h4>Color</h4>
                      <div class="d-flex flex-row align-items-center">
                        <div class="Catalogue__cards__modal__preview-body__form__color" style="background-color: <?php echo strcasecmp($color_scheme, "Monochrome") == 0 ? "#000000" : "linear-gradient(to bottom right, #FF00FF, blue)"; ?>"></div>
                        <p class="Catalogue__cards__modal__preview-body__form__color--tooltip"><?php echo strcasecmp($color_scheme, "Monochrome") == 0 ? "Monochrome" : "Multicolor"; ?></p>
                      </div>
                    </div>
                    <div class="col-md-8">
                      <h4>Complexity</h4>
                      <p><?php echo $complexity ?></p>
                    </div>
                  </div>
                  <div class="my-5 row">
                    <div class="col">
                      <div class="form-floating">
                        <input type="number" class="form-control" name="quantity" min="1" value="1" placeholder="Quantity" required/>
                        <label for="tattoo_width">Order Quantity</label>
                      </div>
                      <label class="error-message quantity_err <?php echo isset($_SESSION['quantity_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><?php if(isset($_SESSION['quantity_err'])) { echo $_SESSION['quantity_err']; } ?></label>
                    </div>
                    <div class="col">
                      <div class="form-floating">
                        <input type="number" class="form-control" name="width" min="1" max="24" value="<?php echo $width ?>" placeholder="Width" required/>
                        <label for="tattoo_width">Width (in inches)</label>
                      </div>
                      <label class="error-message width_err <?php echo isset($_SESSION['width_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><?php if(isset($_SESSION['width_err'])) { echo $_SESSION['width_err']; } ?></label>
                    </div>
                    <div class="col">
                      <div class="form-floating">
                        <input type="number" class="form-control" name="height" min="1" max="36" value="<?php echo $height ?>" placeholder="Height" required/>
                        <label for="tattoo_height">Height (in inches)</label>
                      </div>
                      <label class="error-message height_err <?php echo isset($_SESSION['height_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><?php if(isset($_SESSION['height_err'])) { echo $_SESSION['height_err']; } ?></label>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-dark btn-lg d-flex align-items-center" name="order_item"><span class="material-icons md-48 lh-base pe-2">add_shopping_cart</span>Add to Order</button>
                  <?php if(isset($_SESSION['res'])){ ?>
                    <label class="error-message d-flex"><?php echo $_SESSION['res']; ?></label>
                  <?php } ?>
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
  var catalogue = document.getElementById('Catalogue');
  var cards = document.getElementsByClassName('Catalogue__cards__card');

  // searching for tattoo
  search.addEventListener('input', function (){
    if(search.value.length == 0){
      catalogue.classList.replace('justify-content-evenly', 'justify-content-between');

      for(var i = 0, count = cards.length; i < count; i++){
        cards[i].classList.replace('d-none', 'd-block');
      }
    } else {
      catalogue.classList.replace('justify-content-between', 'justify-content-evenly');

      for(var i = 0, count = cards.length; i < count; i++){
        item_name = cards[i].href.toLowerCase();
        if(item_name.indexOf(search.value.toLowerCase().replaceAll(' ', '%20')) > -1){
          cards[i].classList.replace('d-none', 'd-block');
        } else {
          cards[i].classList.replace('d-block', 'd-none');
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