<?php
require(__DIR__ . "/../../../partials/nav.php"); 


if (!has_role("Admin")) {
    flash("You don't have permission to access this page");
    die(header("Location: ../../login.php"));



}

?>
<?php

    $orderID = se($_GET, "id", -1, false);
    $userID = get_user_id();

$results = [];
   
    $db = getDB();
    $stmt = $db->prepare("SELECT OrderItems.id, order_id, product_id, OrderItems.unit_price, OrderItems.quantity, products.name From OrderItems JOIN products ON products.id = OrderItems.product_id Where order_id=:orderID LIMIT  10");
    $r = $stmt->execute([":orderID"=> $orderID]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results " . var_export($stmt->errorInfo(), true));
    }


?>

<h3 style="text-align: center; margin-top: 20px ;">Thank you! This is your order confirmation page!</h3>
<div class="results" style="margin-left: 50px;">
    <?php if (count($results) > 0): ?>
            <?php foreach ($results as $r): ?>
                <div class="card" style="width: 25rem; height: 25rem;">
                <div class="card-body">
                <h5 class="card-title">Item: <?php echo($r["name"]); ?></h5>
                <div>Price: <?php echo($r["unit_price"]); ?></div>
                <div>Quantity: <?php echo($r["quantity"]); ?></div>
                </div>
            <?php endforeach; ?>
                </div>
         </div>

   
    <?php endif; ?>


<?php
require(__DIR__ . "/../../../partials/flash.php");
?>