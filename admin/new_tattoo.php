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
  <?php require_once '../common/meta.php'; ?>
  <!-- native style -->
  <link href="../style/catalogue.css" rel="stylesheet" scoped>
  <style>
    #preview {
      background-position: center;
      background-repeat: no-repeat;
      background-size: cover;
    }
  </style>
  <title>Catalog New Tattoo | NJC Tattoo</title>
</head>
<body class="w-100 h-100">
  <main>
  <div class="row justify-content-center align-items-start h-100 w-100">
    <div class="order-first d-flex col-5 bg-light vh-100 justify-content-center align-items-center" id="preview">
      <h1 class="text-muted" id="preview_text">Image Preview</h1>
      <div class="position-absolute top-0 start-0 mt-5 ms-5 d-flex align-items-center justify-content-center bg-white border" style="width: 75px; height: 75px;">
        <a href="./catalogue.php" class="stretched-link"><span class="material-icons md-48 display-5" style="width: 24px;">arrow_back_ios</span></a>
      </div>
    </div>
    <div class="order-last d-flex col vh-100 border-start border-1 justify-content-center align-items-center">
      <div class="flex-grow-1">
        <div class="w-65 ms-9">
          <form action="queries.php" method="POST" enctype="multipart/form-data">
            <div class="my-3">
              <input type="text" class="form-control form-control-lg ps-3 fs-display-5 fw-bold" maxlength="50" placeholder="Name" name="tattoo_name" required/>
              <p class="my-2 d-none text-danger"></p>
            </div>
            <div class="input-group my-3">
              <span class="input-group-text">₱</span>
              <input type="number" class="form-control form-control-lg" min="1" name="tattoo_price" value="0.00" placeholder="Price" required/>
              <p class="my-2 d-none text-danger"></p>
            </div>
            <div class="my-3">
              <textarea class="form-control p-3 text-wrap" name="tattoo_description" rows="5" placeholder="Tattoo Description" required></textarea>
              <p class="my-2 d-none text-danger"></p>
            </div>
            <div class="row my-3">
              <div class="col">
                <div class="form-floating">
                  <select name="color_scheme" class="form-select" required>
                    <option value="Monochrome">Monochrome</option>
                    <option value="Multicolor">Multicolor</option>
                  </select>
                  <label for="color_scheme">Color Scheme</label>
                  <p class="my-2 d-none text-danger"></p>
                </div>
              </div>
              <div class="col">
                <div class="form-floating">
                  <select name="complexity_level" class="form-select" required>
                    <option value="Simple">Simple</option>
                    <option value="Complex">Complex</option>
                  </select>
                  <label for="complexity_level">Complexity</label>
                  <p class="my-2 d-none text-danger"></p>
                </div>
              </div>
            </div>
            <div class="row my-3">
              <div class="col">
                <div class="form-floating">
                  <input type="number" class="form-control" placeholder="Width" min="1" max="24" name="tattoo_width" required>
                  <label for="tattoo_width">Width (in inches)</label>
                  <p class="my-2 d-none text-danger"></p>
                </div>
              </div>
              <div class="col">
                <div class="form-floating">
                  <input type="number" class="form-control" placeholder="Height" min="1" max="36" name="tattoo_height" required>
                  <label for="tattoo_height">Height (in inches)</label>
                  <p class="my-2 d-none text-danger"></p>
                </div>
              </div>
            </div>
            <div class="my-3">
              <label for="image" class="form-label text-muted">Tattoo Image</label>
              <input type="file" class="form-control form-control-lg" accept="image/*" onchange="loadFile(event)" name="image" id="image" required/>
              <p class="my-2 d-none text-danger"></p>
            </div>
            <button type="submit" class="btn btn-lg btn-dark" name="catalog_tattoo">Catalog Tattoo</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  </main>
</body>
<script>
  var loadFile = function(event){
    var imageField = document.getElementById('image');
    var preview = document.getElementById('preview');
    var previewText = document.getElementById('preview_text');
    if(imageField.value.length != 0){
      preview.style.backgroundImage = "url('" + URL.createObjectURL(event.target.files[0]) + "')";
      preview.onload = () => { URL.revokeObjectURL(preview.style.backgroundImage); }
      if(!previewText.classList.contains('d-none')){ previewText.classList.add('d-none'); }
    }
  };
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</html>