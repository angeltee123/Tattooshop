<?php
  session_name("sess_id");
  session_start();
  // if(!isset($_SESSION['user']['user_id']) || strcasecmp($_SESSION['user']['user_type'], "Admin") != 0){
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
  <?php require_once '../common/meta.php'; ?>
  <link href="../style/catalogue.css" rel="stylesheet" scoped>
  <style scoped>
    .Catalogue__cards__modal {
      display: flex;
    }

    .Catalogue__cards__modal__preview-image {
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: #f8f9fa;
      order: -1 !important;
    }

    .Catalogue__cards__modal__preview-body__form {
      width: 65% !important;
    }
  </style>
  <!-- native style -->
  <title>Catalog New Tattoo | NJC Tattoo</title>
</head>
<body class="w-100 h-100">
  <div class="Catalogue__cards__modal">
    <div class="Catalogue__cards__modal__preview-image col-5" id="Preview">
      <h1 class="text-muted no-select" id="Preview__text">Image Preview</h1>
      <div class="Catalogue__cards__modal--back">
        <a href="./catalogue.php" class="stretched-link"><span class="material-icons md-48 display-5" style="width: 24px;">arrow_back_ios</span></a>
      </div>
    </div>
    <div class="Catalogue__cards__modal__preview-body col">
      <div class="flex-grow-1">
        <div class="Catalogue__cards__modal__preview-body__form">
          <form id="New-tattoo__form" action="./scripts/php/queries.php" method="POST" enctype="multipart/form-data">
            <div class="my-3">
              <input type="text" class="form-control form-control-lg ps-3 fs-display-5 fw-bold" name="tattoo_name" id="tattoo_name" maxlength="50" placeholder="Name" required/>
              <label id="name_err" class="error-message <?php echo isset($_SESSION['name_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['name_err'])) { echo $_SESSION['name_err']; } ?></span></label>
            </div>
            <div class="input-group mt-3">
              <span class="input-group-text">₱</span>
              <input type="number" class="form-control form-control-lg" name="tattoo_price" id="tattoo_price" min="1" value="0.00" placeholder="Price" required/>
            </div>
            <label id="price_err" class="error-message mb-3 <?php echo isset($_SESSION['price_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['price_err'])) { echo $_SESSION['price_err']; } ?></span></label>
            <div class="my-3">
              <textarea class="form-control p-3 text-wrap" name="tattoo_description" id="tattoo_description" rows="5" placeholder="Tattoo Description" required></textarea>
              <label id="description_err" class="error-message <?php echo isset($_SESSION['description_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['description_err'])) { echo $_SESSION['description_err']; } ?></span></label>
            </div>
            <div class="row my-3">
              <div class="col">
                <div class="form-floating">
                  <select class="form-select" name="color_scheme" id="color_scheme" required>
                    <option value="Monochrome">Monochrome</option>
                    <option value="Multicolor">Multicolor</option>
                  </select>
                  <label for="color_scheme">Color Scheme</label>
                </div>
                <label id="color_scheme_err" class="error-message <?php echo isset($_SESSION['color_scheme_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['color_scheme_err'])) { echo $_SESSION['color_scheme_err']; } ?></span></label>
              </div>
              <div class="col">
                <div class="form-floating">
                  <select class="form-select" name="complexity_level" id="complexity_level" required>
                    <option value="Simple">Simple</option>
                    <option value="Complex">Complex</option>
                  </select>
                  <label for="complexity_level">Complexity</label>
                </div>
                <label id="complexity_level_err" class="error-message <?php echo isset($_SESSION['complexity_level_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['complexity_level_err'])) { echo $_SESSION['complexity_level_err']; } ?></span></label>
              </div>
            </div>
            <div class="row my-3">
              <div class="col">
                <div class="form-floating">
                  <input type="number" class="form-control" name="tattoo_width" id="tattoo_width" min="1" max="24" placeholder="Width" required/>
                  <label for="tattoo_width">Width (in inches)</label>
                </div>
                <label id="width_err" class="error-message <?php echo isset($_SESSION['width_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['width_err'])) { echo $_SESSION['width_err']; } ?></span></label>
              </div>
              <div class="col">
                <div class="form-floating">
                  <input type="number" class="form-control" name="tattoo_height" id="tattoo_height" min="1" max="36" placeholder="Height" required/>
                  <label for="tattoo_height">Height (in inches)</label>
                </div>
                <label id="height_err" class="error-message <?php echo isset($_SESSION['height_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['height_err'])) { echo $_SESSION['height_err']; } ?></span></label>
              </div>
            </div>
            <div class="my-3">
              <label for="image" class="form-label text-muted">Tattoo Image</label>
              <input type="file" class="form-control form-control-lg" accept="image/*" onchange="loadFile(event)" name="image" id="image" required/>
              <label id="image_err" class="error-message <?php echo isset($_SESSION['tattoo_image_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['tattoo_image_err'])) { echo $_SESSION['tattoo_image_err']; } ?></span></label>
            </div>
            <button type="submit" class="btn btn-lg btn-dark" name="catalog_tattoo" id="catalog_tattoo" disabled>Catalog Tattoo</button>
            <?php if(isset($_SESSION['res'])){ ?>
              <label class="error-message d-flex"><?php echo $_SESSION['res']; ?></label>
            <?php } ?>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script src="../api/api.js"></script>
<script src="./scripts/js/new_tattoo.js"></script>
</html>
<?php
  if(isset($_SESSION['name_err'])){
    unset($_SESSION['name_err']);
  }
  if(isset($_SESSION['price_err'])){
    unset($_SESSION['price_err']);
  }
  if(isset($_SESSION['description_err'])){
    unset($_SESSION['description_err']);
  }
  if(isset($_SESSION['complexity_level_err'])){
    unset($_SESSION['complexity_level_err']);
  }
  if(isset($_SESSION['color_scheme_err'])){
    unset($_SESSION['color_scheme-err']);
  }
  if(isset($_SESSION['width_err'])){
    unset($_SESSION['width_err']);
  }
  if(isset($_SESSION['height_err'])){
    unset($_SESSION['height_err']);
  }
  if(isset($_SESSION['quantity_err'])){
    unset($_SESSION['quantity_err']);
  }
  if(isset($_SESSION['tattoo_image_err'])){
    unset($_SESSION['tattoo_image_err']);
  }
  if(isset($_SESSION['res'])){
    unset($_SESSION['res']);
  }
?>