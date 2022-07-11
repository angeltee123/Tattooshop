<?php
  session_name("sess_id");
  session_start();
  // if(!isset($_SESSION['user']['user_id']) || strcasecmp($_SESSION['user']['user_type'], "Admin") != 0){
  //   Header("Location: ../client/index.php");
  //   die();
  // } else {
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
  // }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require_once '../common/meta.php'; ?>
  <link href="../style/catalogue.css" rel="stylesheet" scoped>
  <!-- native style -->
  <style scoped>
    #new_tattoo {
      flex-flow: column nowrap;
      justify-content: center;
      align-items: center;
      border: 1px solid #dee2e6!important;
      background-color: #f8f9fa;
    }

    .Catalogue__cards__modal__preview-body__form {
      width: 65%;
    }
  </style>
  <title>Catalogue | NJC Tattoo</title>
</head>
<body class="w-100">
  <?php require_once '../common/header.php'; ?>
  <div class="Catalogue content">
    <div class="Catalogue__header">
      <h2 class="fw-bold display-3">Catalogue</h2>
      <p class="fs-5 text-muted">Manage your catalogue of tattoos and add new tattoos to it.</p>
    </div>
    <div class="Catalogue__search-bar">
      <div class="material-icons position-absolute fs-2 ms-4 no-select">search</div>
      <input type="text" class="Catalogue__search-bar__input form-control form-control-lg" id="search" placeholder="Search">
    </div>
    <div class="Catalogue__cards justify-content-between" id="Catalogue">
      <a class="Catalogue__cards__card shadow-sm order-first d-flex text-center" id="new_tattoo" href="./new_tattoo.php">
        <h1 class="my-0"><span class="material-icons md-48 display-1">add</span></h1>
        <h3>Add New Tattoo</h3>
      </a>
      <?php
        if($api->num_rows($res)){
          while($tattoo = $api->fetch_assoc($res)){
            $id = $api->sanitize_data($tattoo['tattoo_id'], "string");
            $name = $api->sanitize_data($tattoo['tattoo_name'], "string");
            $price = number_format($api->sanitize_data($tattoo['tattoo_price'], "float"), 2, '.', '');
            $height = $api->sanitize_data($tattoo['tattoo_height'], "int");
            $width = $api->sanitize_data($tattoo['tattoo_width'], "int");
            $image = $api->sanitize_data($tattoo['tattoo_image'], "string");
            $description = $api->sanitize_data($tattoo['tattoo_description'], "string");
            $color_scheme = $api->sanitize_data($tattoo['color_scheme'], "string");
            $complexity = $api->sanitize_data($tattoo['complexity_level'], "string");  
      ?>
        <a class="Catalogue__cards__card shadow-sm d-block" href="#<?php echo $name; ?>" style="background-image: url(<?php echo $image; ?>)"></a>
        <!-- deletion-modal -->
        <div class="modal fade" id="delete_<?php echo $id; ?>" tabindex="-1" aria-labelledby="delete_<?php echo $name; ?>_label" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="#delete_<?php echo $name; ?>_label">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <p>Are you sure you want to delete this tattoo from the catalogue?</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="./scripts/php/queries.php" method="POST">
                  <input type="hidden" class="d-none" name="tattoo_id" value="<?php echo $id; ?>">
                  <button type="submit" class="btn btn-outline-danger" name="delete_tattoo">Yes, delete it</button>
                </form>
              </div>
            </div>
          </div>
        </div>
        <div id="<?php echo $name; ?>" class="Catalogue__cards__modal">
          <div class="Catalogue__cards__modal__preview-image col-5" style="background-image: url(<?php echo $image; ?>)" id="preview_<?php echo $id; ?>"></div>
          <div class="Catalogue__cards__modal__preview-body col">
            <div class="flex-grow-1">
              <div class="Catalogue__cards__modal--back">
                <a href="./catalogue.php" class="stretched-link"><span class="material-icons md-48 display-5">close</span></a>
              </div>
              <div class="Catalogue__cards__modal__preview-body__form">
                <form class="Catalogue__cards__modal__form" action="queries.php" method="POST" enctype="multipart/form-data">
                  <input type="hidden" class="d-none" name="tattoo_id" value="<?php echo $id; ?>" required/>
                  <div class="my-3">
                    <input type="text" class="form-control form-control-lg ps-3 fs-display-5 fw-bold" name="tattoo_name" maxlength="50" value="<?php echo $name; ?>" placeholder="Name" required/>
                    <label class="error-message name_err <?php echo isset($_SESSION['name_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['name_err'])) { echo $_SESSION['name_err']; } ?></span></label>
                  </div>
                  <div class="input-group mt-3">
                    <span class="input-group-text">₱</span>
                    <input type="number" class="form-control form-control-lg" min="1" name="tattoo_price" value="<?php echo $price; ?>" placeholder="Price" required/>
                  </div>
                  <label class="error-message mb-3 price_err <?php echo isset($_SESSION['price_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['price_err'])) { echo $_SESSION['price_err']; } ?></span></label>
                  <div class="my-3">
                    <textarea class="form-control p-3 text-wrap text-justify" name="tattoo_description" rows="5" placeholder="Tattoo Description" required><?php echo $description; ?></textarea>
                    <label class="error-message description_err <?php echo isset($_SESSION['description_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['description_err'])) { echo $_SESSION['description_err']; } ?></span></label>
                  </div>
                  <div class="row my-3">
                    <div class="col">
                      <div class="form-floating">
                        <select name="color_scheme" class="form-select" required>
                          <option value="Monochrome" <?php if(strcasecmp($color_scheme, 'Monochrome') == 0){ echo "selected"; } ?>>Monochrome</option>
                          <option value="Multicolor" <?php if(strcasecmp($color_scheme, 'Multicolor') == 0){ echo "selected"; } ?>>Multicolor</option>
                        </select>
                        <label for="color_scheme">Color Scheme</label>
                      </div>
                      <label class="error-message color_scheme_err <?php echo isset($_SESSION['color_scheme_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['color_scheme_err'])) { echo $_SESSION['color_scheme_err']; } ?></span></label>
                    </div>
                    <div class="col">
                      <div class="form-floating">
                        <select name="complexity_level" class="form-select" required>
                          <option value="Simple" <?php if(strcasecmp($complexity, 'Simple') == 0){ echo "selected"; } ?>>Simple</option>
                          <option value="Complex" <?php if(strcasecmp($complexity, 'Complex') == 0){ echo "selected"; } ?>>Complex</option>
                        </select>
                        <label for="complexity_level">Complexity</label>
                      </div>
                      <label class="error-message complexity_level_err <?php echo isset($_SESSION['complexity_level_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['complexity_level_err'])) { echo $_SESSION['complexity_level_err']; } ?></span></label>
                    </div>
                  </div>
                  <div class="row my-3">
                    <div class="col">
                      <div class="form-floating">
                        <input type="number" class="form-control" name="tattoo_width" min="1" max="24" value="<?php echo $width; ?>" placeholder="Width" required/>
                        <label for="tattoo_width">Width (in inches)</label>
                      </div>
                      <label class="error-message width_err <?php echo isset($_SESSION['width_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['width_err'])) { echo $_SESSION['width_err']; } ?></span></label>
                    </div>
                    <div class="col">
                      <div class="form-floating">
                        <input type="number" class="form-control" name="tattoo_height" min="1" max="36" value="<?php echo $height; ?>" placeholder="Height" required/>
                        <label for="tattoo_height">Height (in inches)</label>
                      </div>
                      <label class="error-message height_err <?php echo isset($_SESSION['height_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['height_err'])) { echo $_SESSION['height_err']; } ?></span></label>
                    </div>
                  </div>
                  <div class="my-3">
                    <label for="image" class="form-label text-muted">Tattoo Image</label>
                    <input type="file" class="form-control form-control-lg" accept="image/*" name="image" id="image_<?php echo $id; ?>" onchange="loadpreview_<?php echo $id; ?>(event)"/>
                    <label class="error-message image_err <?php echo isset($_SESSION['tattoo_image_err']) ? "d-flex": "d-none"; ?>"><span class="material-icons-outlined fs-6 me-1">info</span><span><?php if(isset($_SESSION['tattoo_image_err'])) { echo $_SESSION['tattoo_image_err']; } ?></span></label>
                    <?php echo "<script>var loadpreview_" . $id . " = function(event){ var image_" . $id . " = document.getElementById('image_". $id ."'); var preview_" . $id . " = document.getElementById('preview_". $id ."'); if(image_". $id .".value.length != 0){ preview_" . $id . ".style.backgroundImage = 'url(' + URL.createObjectURL(event.target.files[0]) + ')'; preview_" . $id . ".onload = () => { URL.revokeObjectURL(preview_" . $id . ".style.backgroundImage); }} else { preview_" . $id . ".style.backgroundImage = 'url(". $image .")'; }};</script>"; ?>
                  </div>
                  <button type="submit" class="btn btn-primary btn-lg" name="update_tattoo">Save Changes</button>
                  <button type="button" class="btn btn-outline-danger btn-lg" data-bs-toggle="modal" data-bs-target="#delete_<?php echo $id; ?>">Delete</button>
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
<script src="../api/api.js"></script>
<script src="./scripts/js/catalogue.js"></script>
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
    unset($_SESSION['color_scheme_err']);
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