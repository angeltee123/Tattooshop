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
    <link href="../style/catalogue.css" rel="stylesheet" scoped>
    <title>Catalogue | NJC Tattoo</title>
</head>
<body class="w-100">
  <header class="header">
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
  <div class="content w-70">
    <div class="text-center mb-8">
      <h2 class="fw-bold display-3">Catalogue</h2>
      <p class="fs-5 text-muted">Manage your catalogue of tattoos and add new tattoos to it.</p>
    </div>
    <div class="mb-4 position-relative d-flex flex-row justify-content-start align-items-center">
      <div class="material-icons position-absolute fs-2 ms-4 no-select">search</div>
      <input type="text" class="form-control form-control-lg rounded-pill" style="padding-top: 1.25rem; padding-left: 4.5rem; padding-bottom: 1.25rem; padding-right: 1.25rem;" id="search" placeholder="Search">
    </div>
    <div class="w-100 d-flex flex-row flex-wrap justify-content-between align-items-start" id="catalogue">
      <a class="order-first tattoo-card d-flex bg-light my-4 border shadow-sm rounded flex-column justify-content-center align-items-center" id="new_tattoo" href="./new_tattoo.php">
        <h1 class="my-0"><span class="material-icons md-48 display-1">add</span></h1>
        <h3>Add New Tattoo</h3>
      </a>
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
        } catch (Exception $e){
          exit;
          Header("Location: ./index.php");
          echo $e->getMessage();
        }

        if($api->num_rows($res)){
          while($row = $api->fetch_assoc($res)){
            $id = $api->sanitize_data($row['tattoo_id'], "string");
            $name = $api->sanitize_data($row['tattoo_name'], "string");
            $price = number_format($api->sanitize_data($row['tattoo_price'], "float"), 2, '.', '');
            $height = $api->sanitize_data($row['tattoo_height'], "int");
            $width = $api->sanitize_data($row['tattoo_width'], "int");
            $image = $api->sanitize_data($row['tattoo_image'], "string");
            $description = $api->sanitize_data($row['tattoo_description'], "string");
            $color_scheme = $api->sanitize_data($row['color_scheme'], "string");
            $complexity = $api->sanitize_data($row['complexity_level'], "string");  
      ?>
        <a class="tattoo-card my-4 d-block shadow-sm rounded" href="#<?php echo $name; ?>" style="background-image: url(<?php echo $image; ?>)"></a>
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
                <form action="./queries.php" method="POST">
                  <input type="hidden" class="d-none" name="tattoo_id" value="<?php echo $id; ?>">
                  <button type="submit" class="btn btn-outline-danger" name="delete_tattoo">Yes, delete it</button>
                </form>
              </div>
            </div>
          </div>
        </div>
        <div id="<?php echo $name; ?>" class="tattoo_detail row justify-content-center align-items-start h-100 w-100">
          <div class="order-first tattoo-image col-5 bg-light" style="background-image: url(<?php echo $image; ?>)" id="preview_<?php echo $id; ?>"></div>
          <div class="order-last d-flex col vh-100 border-start border-1 justify-content-center align-items-center">
            <div class="flex-grow-1">
              <div class="position-absolute top-0 start-0 mt-5 ms-5 d-flex align-items-center justify-content-center bg-white border" style="width: 75px; height: 75px;">
                <a href="./catalogue.php" class="stretched-link"><span class="material-icons md-48 display-5">close</span></a>
              </div>
              <div class="w-65 ms-9">
                <form action="queries.php" method="POST" enctype="multipart/form-data">
                  <input type="hidden" class="d-none" name="tattoo_id" value="<?php echo $id; ?>">
                  <div class="my-3">
                    <input type="text" class="form-control form-control-lg ps-3 fs-display-5 fw-bold" maxlength="50" value="<?php echo $name; ?>" placeholder="Name" name="tattoo_name" required/>
                    <p class="my-2 d-none text-danger"></p>
                  </div>
                  <div class="input-group my-3">
                    <span class="input-group-text">₱</span>
                    <input type="number" class="form-control form-control-lg" min="1" name="tattoo_price" value="<?php echo $price; ?>" placeholder="Price" required/>
                    <p class="my-2 d-none text-danger"></p>
                  </div>
                  <div class="my-3">
                    <textarea class="form-control p-3 text-wrap" name="tattoo_description" rows="5" placeholder="Tattoo Description" required><?php echo $description; ?></textarea>
                    <p class="my-2 d-none text-danger"></p>
                  </div>
                  <div class="row my-3">
                    <div class="col">
                      <div class="form-floating">
                        <select name="color_scheme" class="form-select" required>
                        <option value="Monochrome" <?php if(strcasecmp($color_scheme, 'Monochrome') == 0){ echo "selected"; } ?>>Monochrome</option>
                        <option value="Multicolor" <?php if(strcasecmp($color_scheme, 'Multicolor') == 0){ echo "selected"; } ?>>Multicolor</option>
                        </select>
                        <label for="color_scheme">Color Scheme</label>
                        <p class="my-2 d-none text-danger"></p>
                      </div>
                    </div>
                    <div class="col">
                      <div class="form-floating">
                        <select name="complexity_level" class="form-select" required>
                          <option value="Simple" <?php if(strcasecmp($complexity, 'Simple') == 0){ echo "selected"; } ?>>Simple</option>
                          <option value="Complex" <?php if(strcasecmp($complexity, 'Complex') == 0){ echo "selected"; } ?>>Complex</option>
                        </select>
                        <label for="complexity_level">Complexity</label>
                        <p class="my-2 d-none text-danger"></p>
                      </div>
                    </div>
                  </div>
                  <div class="row my-3">
                    <div class="col">
                      <div class="form-floating">
                        <input type="number" class="form-control" placeholder="Width" min="1" max="24" value="<?php echo $width; ?>" name="tattoo_width" required>
                        <label for="tattoo_width">Width (in inches)</label>
                        <p class="my-2 d-none text-danger"></p>
                      </div>
                    </div>
                    <div class="col">
                      <div class="form-floating">
                        <input type="number" class="form-control" placeholder="Height" min="1" max="36" value="<?php echo $height; ?>" name="tattoo_height" required>
                        <label for="tattoo_height">Height (in inches)</label>
                        <p class="my-2 d-none text-danger"></p>
                      </div>
                    </div>
                  </div>
                  <div class="my-3">
                    <label for="image" class="form-label text-muted">Tattoo Image</label>
                    <input type="file" class="form-control form-control-lg" accept="image/*" onchange="loadpreview_<?php echo $id; ?>(event)" name="image" id="image_<?php echo $id; ?>"/>
                    <p class="my-2 d-none text-danger"></p>
                    <?php echo "<script>var loadpreview_" . $id . " = function(event) { var image_" . $id . " = document.getElementById('image_". $id ."'); var preview_" . $id . " = document.getElementById('preview_". $id ."'); if(image_". $id .".value.length != 0) { preview_" . $id . ".style.backgroundImage = 'url(' + URL.createObjectURL(event.target.files[0]) + ')'; preview_" . $id . ".onload = () => { URL.revokeObjectURL(preview_" . $id . ".style.backgroundImage); }} else { preview_" . $id . ".style.backgroundImage = 'url(". $image .")'; }};</script>"; ?>
                  </div>
                  <button type="submit" class="btn btn-primary btn-lg" name="update_tattoo">Save Changes</button>
                  <button type="button" class="btn btn-outline-danger btn-lg" data-bs-toggle="modal" data-bs-target="#delete_<?php echo $id; ?>">Delete</button>
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
<script src="../api/bootstrap-bundle-min.js"></script>
<script>
  // search bar
  var search = document.getElementById('search');

  // new tattoo card
  var new_tattoo = document.getElementById('new_tattoo');

  // catalogue
  var catalogue = document.getElementById('catalogue');
  var cards = document.getElementsByClassName('tattoo-card');

  // searching for tattoo
  search.addEventListener('input', function () {
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
        if(item_name.indexOf(search.value.toLowerCase()) > -1 && cards[i] !== new_tattoo){
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