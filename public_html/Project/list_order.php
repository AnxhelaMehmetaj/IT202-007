<?php
require(__DIR__ . "/../../partials/nav.php"); ?>

<?php
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
?>
<?php


$results = [];
$orderitems_results = [];
$db = getDB();
$sql_str = "";
if (has_role("Admin")) {
    $sql_str = "SELECT id, user_id, total_price, created, payment_method, address FROM orders WHERE user_id = :user_id OR NOT user_id = :user_id LIMIT 10";
} else {
    $sql_str = "SELECT id, user_id, total_price, created, payment_method, address FROM orders WHERE user_id = :user_id LIMIT 10";
}
$stmt = $db->prepare($sql_str);
try {
    $stmt->execute([":user_id" => $_SESSION["user"]["id"]]);
    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($r) {
        $results = $r;
    }
} catch (PDOException $e) {
    flash("<pre>" . var_export($e, true) . "</pre>");
}
?>
<h1>Order History</h1>
<?php
foreach ($results as $index => $value) : ?>

    <div class='card'>
        <br>Order <?php echo $value["id"] ?> 
        <div> Date and time <?php echo $value["created"] ?> </div>
         Total price: <?php echo $value["total_price"] ?>
        <br>Payment Method: <?php echo $value["payment_method"]?>
        <br>Address: <?php echo$value["address"] ?>
        <br><a href="view_order.php?id= <?php echo $value["id"] ?> ">Order Info</a>
    </div><br>

<?php endforeach; ?>
<?php
require(__DIR__ . "/../../partials/flash.php");
?>