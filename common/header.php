<?php $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']; ?>
<nav class="navbar navbar-expand-lg p-0">
  <div class="container-fluid">
    <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav__links"><span class="material-icons display-4">menu</span></button>
    <div class="collapse navbar-collapse" id="nav__links">
      <div class="navbar-nav">
        <a href="./<?php echo strcasecmp($_SESSION['user']['user_type'], "User") == 0 ? "explore" : "catalogue"; ?>.php" class="nav-link <?php if(strpos($url,'explore') !== false || strpos($url,'catalogue') !== false) { echo "nav-link--active"; } ?>"><?php echo strcasecmp($_SESSION['user']['user_type'], "User") == 0 ? "Explore" : "Catalogue"; ?></a>
        <a href="./orders.php" class="nav-link <?php if(strpos($url,'orders') !== false || strpos($url,'checkout') !== false) { echo "nav-link--active"; } ?>">Orders</a>
        <a href="./reservations.php" class="nav-link <?php if(strpos($url,'reservations') !== false) { echo "nav-link--active"; } ?>">Bookings</a>
        <?php if(strcasecmp($_SESSION['user']['user_type'], "User") == 0){ ?>
          <a href="./profile.php" class="nav-link nav-link--hidden">Profile</a>
        <?php } else { ?>
          <a href="./index.php" class="nav-link nav-link--hidden">Analytics</a>
        <?php } ?>
        <form action="../scripts/php/queries.php" method="post">
          <button type="submit" class="nav-link nav-link--hidden" name="logout">Sign Out</button>
        </form>
      </div>
    </div>
    <div class="navbar-nav">
      <div class="btn-group" id="nav-user">
        <button type="button" class="btn p-0" data-bs-toggle="dropdown" aria-expanded="false"><div id="user-avatar" class="avatar" style="background-image: url(<?php echo $_SESSION['user']['user_avatar']; ?>)"></div></button>
          <ul class="dropdown-menu dropdown-menu-end">
            <?php if(strcasecmp($_SESSION['user']['user_type'], "User") == 0){ ?>
              <li><a class="dropdown-item" href="profile.php">Profile</a></li>
            <?php } else { ?>
              <li><a class="dropdown-item" href="index.php">Analytics</a></li>
            <?php } ?>
            <li>
              <form action="../scripts/php/queries.php" method="post">
                <button type="submit" class="dropdown-item btn-link" name="logout">Sign Out</button>
              </form>
            </li>
          </ul>
      </div>
    </div>
  </div>
</nav>