<?php

require(__DIR__ . "/../../../partials/nav.php");



if (!has_role("Admin")) {
    flash("You don't have permission to access this page");
    die(header("Location: ../../login.php"));
}
?>

<?php

$userID = get_user_id();
$cartID = 0;
$productID = 0;
$results = [];
$quantity = 0;
$subtotal = 0;
$address = "";

$db = getDB();
    $stmt = $db->prepare("SELECT payment_method, total_price, user_id, id, address From orders WHERE user_id = :user_id LIMIT 10");
    $r = $stmt->execute([":user_id"=> $userID,]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results " . var_export($stmt->errorInfo(), true));
    }



?>
    <h3>Order History</h3>
    <div class="results">
        <?php if (count($results) > 0): ?>
                <?php foreach ($results as $r): ?>
             <div class="card" style="width: 18rem;">
                    <div class="card-body">
                    <h5 class="card-title">Order Number : <?php echo($r["id"]); ?></h5>
                    </div>
                        <div>
                            <a type="button" href="view_order.php?id=<?php echo($r['id']); ?>">View Order</a>
                        </div>
                        </div>
                <?php endforeach; ?>
              
                   
            <div class="card" style="width: 18rem;">
                <div class="card-body">
                    <h5 class="card-title">Total Price:<?php echo($r["total_price"]); ?></h5>
                </div> 
            </div> 
           
               <form method="POST">
                <div class="form-group">
                <input type="submit" name="clearAll" value="Empty Cart"/>
                </form>

        <?php endif; ?>
        </div>
                </div>
                <?php
require(__DIR__ . "/../../../partials/flash.php");
?>