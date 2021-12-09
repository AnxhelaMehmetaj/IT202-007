<?php
require(__DIR__ . "/../../partials/nav.php"); ?>
<?php

//php block have access to it
$productID=se($_GET, "id", -1, false);
$unit_price = 0;
    $desired_quantity = 0;
    $user_id = get_user_id();

if(isset($_POST['desired_quantity'])){
    $desired_quantity = $_POST['desired_quantity'];
}

//fetching
$result = [];
if (isset($productID)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT name, unit_price, quantity, description, id FROM products where id = :id");
    $r = $stmt->execute([":id" => $productID]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}
$unit_price = $result["unit_price"];
    $product_name = $result["name"];
if (isset($_POST["save"])) {
    $stmt = $db->prepare("INSERT INTO cart (product_id, user_id, desired_quantity, unit_price) VALUES(:product_id, :user_id, :desired_quantity, :unit_price) on DUPLICATE KEY UPDATE desired_quantity=desired_quantity + :desired_quantity");
    try {
        $stmt->execute([":product_id" => $productID, ":user_id" => $user_id, ":desired_quantity" => $desired_quantity, ":unit_price" => $unit_price]);
        flash("Added to cart");
    } catch (Exception $e) {
        flash("<pre>" . var_export($e, true) . "</pre>");
    } 
}
?>


<?php if (isset($result) && !empty($result)): ?>
    <div class="card" style="width: 20rem; ">
        <div class="card-body">
        <h5 class="card-title"><?php echo($result["name"]); ?></h5>
    
                <div>Price: <?php echo($result["unit_price"]); ?></div>
                <div>Quantity: <?php echo($result["quantity"]); ?></div>
                <?php if ($result["quantity"] < 10): ?>   
                        <div><?php echo("Only " . $result["quantity"] . " left in stock, order soon."); ?></div>
                   <?php endif;?>
                <div>Description: <?php echo($result["description"]); ?></div></p>
                <?php  if (is_logged_in()): ?>
                <form method="POST">
                    <div class="form-group">
                      
                         Quantity: <input type="number" min="0" name="desired_quantity" value="?>"/>
                    </div>
                

                        <input type="submit" name="save" value="Add to Cart"/>
                        <input type="hidden" name="price" value="<?php echo $result["unit_price"]; ?>"/>
                       
                </form>
                <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<?php
require(__DIR__ . "/../../partials/flash.php");
?>