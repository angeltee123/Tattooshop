<header class="header">
  <nav class="nav-bar row mx-0">
    <ul class="col my-0" id="nav-links">
      <li class="active"><a href="./explore.php">Explore</a></li>
      <li><a href="orders.php">Orders</a></li>
      <li><a href="reservations.php">Bookings</a></li>
    </ul>
    <div class="col d-flex align-items-center justify-content-end my-0 mx-5">
      <div class="btn-group" id="nav-user">
        <button type="button" class="btn p-0" data-bs-toggle="dropdown" aria-expanded="false"><div class="user-avatar bg-center-fit rounded-pill" style="background-image: url(<?php echo $_SESSION['user']['user_avatar']; ?>)"></div></button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
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