<?php include_once '../api/connection.php' ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link href="./style/style.css" rel="stylesheet">
    <title>Orders</title>
</head>
<body>
<?php
if(!isset($amount_total)){
  $amount_total = 0.00;
}

// $select = "SELECT `workorder`.`order_id`, `order_item`.`item_id`, `workorder`.`order_date`, `tattoo`.`tattoo_id`, `tattoo`.`tattoo_name`, `tattoo`.`tattoo_description`, `order_item`.`tattoo_quantity`, `tattoo`.`tattoo_price` FROM ((`order_item` INNER JOIN `tattoo` ON `order_item`.`tattoo_id`=`tattoo`.`tattoo_id`) INNER JOIN `workorder` ON `workorder`.`order_id`=`order_item`.`order_id`) WHERE `order`.`client_id`=$user_id";
$select = "SELECT `workorder`.`order_id`, `order_item`.`item_id`, `workorder`.`order_date`, `tattoo`.`tattoo_id`, `tattoo`.`tattoo_name`, `tattoo`.`tattoo_description`, `order_item`.`tattoo_quantity`, `tattoo`.`tattoo_price` FROM ((`order_item` INNER JOIN `tattoo` ON `order_item`.`tattoo_id`=`tattoo`.`tattoo_id`) INNER JOIN `workorder` ON `workorder`.`order_id`=`order_item`.`order_id`)";
$res = mysqli_query($conn, $select);

?>
<table class="table w-75">
  <thead class="align-middle" style="height: 4em;">
    <tr>
      <th scope="col">Actions</th>
      <th scope="col">Order ID</th>
      <th scope="col">Date Ordered</th>
      <th scope="col">Tattoo Name</th>
      <th scope="col">Description</th>
      <th scope="col">Quantity</th>
      <th scope="col">Price</th>
    </tr>
  </thead>
  <tbody>
    <?php
      if(mysqli_num_rows($res) > 0){
        while($row = mysqli_fetch_assoc($res)){
          $amount_total+= $row['tattoo_price'] * $row['tattoo_quantity'];
    ?>
    <tr class="align-middle" style="height: 5em;">
      <td>
        <form method="POST" action="../api/queries.php">
          <input type="hidden" class="d-none" name="item_id" value="<?php echo $row['item_id']?>"/>
          <button class="btn btn-outline-info" name="update_item">Update</button>
          <button class="btn btn-outline-danger" name="remove_item">Remove</button>
        </form>
      </td>
      <th scope="row"><?php echo $row['order_id']?></th>
      <td><?php // echo htnlspecialchars($row['order_date'] ?></td>
      <td><?php echo $row['tattoo_name']?></td>
      <td><?php echo $row['tattoo_description']?></td>
      <td><?php echo $row['tattoo_quantity']?></td>
      <td>Php <?php echo $row['tattoo_price']?></td>
    </tr>
    <?php }} ?>
    <tfoot style="height: 4em;">
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td class="fw-bold">Amount Total</td>
        <td>Php <?php echo number_format($amount_total, 2, '.', '') ?></td>
    </tfoot>
  </tbody>
</table>
</body>
</html>