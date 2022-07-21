<?php
  session_name("sess_id");
  session_start();

  // navigation guard
  if(!isset($_SESSION['user']['user_id']) || strcasecmp($_SESSION['user']['user_type'], "User") != 0){
    Header("Location: ../admin/index.php");
    die();
  } else {
    require_once '../api/api.php';
    $api = new api();

    // retrieve tattoos from catalog
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

  // retrieve client workorder
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
  <!-- meta -->
  <?php require_once '../common/meta.php'; ?>
  
  <!-- external stylesheets -->
  <link href="../style/catalogue.css" rel="stylesheet" scoped>
  
  <!-- native style -->
  <link href="./style/explore.css" rel="stylesheet" scoped>
  <title>Explore | NJC Tattoo</title>
</head>
<body>
  <!-- navigation bar -->
  <?php require_once '../common/header.php'; ?>
  
  <!-- page content -->
  <div class="Catalogue content">

    <!-- page header -->
    <div class="Catalogue__header">
      <h2 class="fw-bold display-3">Explore</h2>
      <p class="fs-5 text-muted">Find your next tattoo here.</p>
    </div>

    <!-- search bar -->
    <div class="Catalogue__search-bar">
      <div class="material-icons position-absolute fs-2 ms-4 no-select">search</div>
      <input type="text" class="Catalogue__search-bar__input form-control form-control-lg" id="search" placeholder="Search">
    </div>

    <!-- tattoo cards -->
    <div class="Catalogue__cards justify-content-between" id="Catalogue">
      <?php
        // extract sql row data
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
        <!-- tattoo card -->
        <a class="Catalogue__cards__card shadow-sm d-block" href="#<?php echo $name?>" style="background-image: url(<?php echo $image; ?>)"></a>
        
        <!-- tattoo modal -->
        <div id="<?php echo $name?>" class="Catalogue__cards__modal overflow-auto">
          
          <!-- tattoo preview -->
          <div class="Catalogue__cards__modal__preview" style="width: 80%;">
            <div class="Catalogue__cards__modal__preview__image" style="background-image: url(<?php echo $image; ?>);"></div>
          </div>

          <!-- tattoo details -->
          <div class="Catalogue__cards__modal__preview-body flex-grow-1">
            <!-- close modal -->
            <div class="Catalogue__cards__modal--back">
              <a href="./explore.php" class="stretched-link"><span class="material-icons md-48 display-5" style="width: 24px;">arrow_back_ios</span></a>
            </div>

            <!-- modal form -->
            <div class="Catalogue__cards__modal__preview-body__form">
              <form class="Catalogue__cards__modal__form" action="../scripts/php/queries.php" method="POST">
                <input type="hidden" class="d-none" name="tattoo_id" value="<?php echo $id ?>" required/>
                <input type="hidden" class="d-none" name="tattoo_name" value="<?php echo $id ?>" required/>
                
                <!-- tattoo details -->
                <div>
                  <h1 class="Catalogue__cards__modal__preview-body__name display-4 fw-bold my-0"><?php echo $name ?></h1>
                  <h4 class="Catalogue__cards__modal__preview-body__price text-secondary">₱<?php echo $price ?> • <?php echo $complexity ?> • <?php echo $color_scheme ?></h4>
                </div>

                <!-- description -->
                <div class="Catalogue__cards__modal__preview-body__description text-justify">
                  <p class="fs-6 text-wrap"><?php echo $description ?></p>
                </div>

                <div class="Catalogue__cards__modal__preview-body__fields row">
                  <!-- item quantity -->
                  <div class="col-12 col-lg my-2">
                    <div class="form-floating">
                      <input type="number" class="form-control<?php if(isset($_SESSION['quantity_err'])) echo " is-invalid"; ?>" name="quantity" min="1" placeholder="Quantity" required/>
                      <label for="tattoo_width">Order Quantity</label>
                    </div>
                    <label class="error-message quantity_err <?php echo isset($_SESSION['quantity_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['quantity_err'])) { echo $_SESSION['quantity_err']; } ?></span></label>
                  </div>

                  <!-- tattoo width -->
                  <div class="col-12 col-lg my-2">
                    <div class="form-floating">
                      <input type="number" class="form-control<?php if(isset($_SESSION['width_err'])) echo " is-invalid"; ?>" name="width" min="1" max="24" value="<?php echo $width ?>" placeholder="Width" required/>
                      <label for="tattoo_width">Width (in inches)</label>
                    </div>
                    <label class="error-message width_err <?php echo isset($_SESSION['width_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['width_err'])) { echo $_SESSION['width_err']; } ?></span></label>
                  </div>

                  <!-- tattoo height -->
                  <div class="col-12 col-lg my-2">
                    <div class="form-floating">
                      <input type="number" class="form-control"<?php if(isset($_SESSION['height_err'])) echo " is-invalid"; ?> name="height" min="1" max="36" value="<?php echo $height ?>" placeholder="Height" required/>
                      <label for="tattoo_height">Height (in inches)</label>
                    </div>
                    <label class="error-message height_err <?php echo isset($_SESSION['height_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['height_err'])) { echo $_SESSION['height_err']; } ?></span></label>
                  </div>
                </div>

                <!-- order item -->
                <button type="submit" class="btn btn-dark btn-lg d-flex align-items-center" name="order_item"><span class="material-icons md-48 lh-base pe-2">add_shopping_cart</span>Add to Order</button>
                
                <?php if(isset($_SESSION['res'])){ ?>
                  <label class="error-message d-flex"><?php echo $_SESSION['res']; ?></label>
                <?php } ?>
              </form>
            </div>
          </div>
        </div>
      <?php }} ?>
    </div>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script src="../scripts/js/explore.js"></script>
</html>
<?php
  // refresh back-end validation feedback
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