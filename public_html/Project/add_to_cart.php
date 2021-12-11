<?php
require(__DIR__ . "/../../partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
?>


<?php
$userID = get_user_id();
$cartID = 0;
$productID = 0;
$results = [];
$quantity = 0;
$subtotal = 0;
$has_error=false;

 

if (isset($_POST["query"])) {
    $query = $_POST["query"];
}

?>
<?php   
    if(isset($_POST["save"])) {
        $quantity = (int)$_POST["quantity"];
        if($quantity == 0) {
            $cartID = $_POST["id"];
            $db = getDB();
            $stmt = $db->prepare("DELETE From cart where id = :cartID");
            $r = $stmt->execute([":cartID"=> $cartID,]);
        }
        if ($quantity != 0 ) {
            $productID = $_POST["product_id"];
            $db = getDB();
            $stmt = $db->prepare("INSERT into cart (`product_id`, `user_id`, `desired_quantity`) VALUES (:productID, :userID, :quantity) on DUPLICATE KEY UPDATE desired_quantity= :quantity");
            $r = $stmt->execute([
                ":productID" => $productID,
                ":userID" => $userID,
                ":quantity" => $quantity
                ]);
    }
    }
    if(isset($_POST["clearAll"])) {
        $db = getDB();
        $stmt = $db->prepare("DELETE from cart where user_id = :userID");
        $r = $stmt->execute([":userID"=> $userID,]);
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT cart.unit_price as cart_price, name, product_id,   products.unit_price as product_price, cart.id, cart.desired_quantity, products.stock From
      cart JOIN products on cart.product_id = products.id where cart.user_id=:user_id LIMIT 10");
    
    
    
    $r = $stmt->execute([":user_id"=> $userID,]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results " . var_export($stmt->errorInfo(), true));
    }
    
    
 

?>

<h3>Your Cart</h3>
<div class="results">
    <?php if (count($results) > 0): ?>
            <?php foreach ($results as $r): ?>
               <?php  $subtotal += ($r["cart_price"]*$r["desired_quantity"]); ?>
                <div class="card" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title"><?php echo($r["name"]); ?></h5>
                <div>Price: <?php echo(($r["cart_price"]* $r["desired_quantity"])); ?></div>
                <div>Desired_quantity: <?php echo($r["desired_quantity"]); ?></div>
                
                <?php if ($r["cart_price"] != $r["product_price"] ): ?>
                    <?php flash("Quantity of price changed!");
                    $has_error = true; 
                    endif; ?>
                     


                </div>
                <form method="POST">
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" name="quantity" value="<?php echo $r["desired_quantity"]; ?>"/>
                   
                   
                    <?php if ($r["stock"] < $r["desired_quantity"]) : ?>
                    <?php flash("Quantity of " . $r["name"] . " too high, please enter a lower quantity");
                    $has_error = true; 
                    endif; ?>
                    
                   
                   
                
                <input type="submit" name="save" value="Update Quantity"/>
                    <input type="hidden" name="product_id" value="<?php echo $r["product_id"]; ?>"/>  
                    <input type="hidden" name="id" value="<?php echo $r["id"]; ?>"/>
                </div>
                </form>
                <form method="POST">
                    <input type="hidden" name="quantity" value="0"/>
                    <input type="submit" name= "clearAll" value="Remove Item"/>
                    <input type="hidden" name="product_id" value="<?php echo $r["product_id"]; ?>"/>
                    <input type="hidden" name="id" value="<?php echo $r["id"]; ?>"/>        
                </form>
                    
            <?php endforeach; ?>
          
        </div>
        <div class="card" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title">Subtotal:<?php echo($subtotal); ?></h5>
                <input type="hidden" name="pquantity" value="<?php echo $r["desired_quantity"]; ?>"/>
        <input type="hidden" name="stock" value="<?php echo $r["stock"]; ?>"/>
        <div>    
            <?php if (!$has_error) :?>
            <a type="button" href="checkout.php">Checkout</a> <?php else: ?>
                <a type="button" href="#">Checkout</a>     
            <?php endif;  ?>
            </div>
        </div> </div> </div>
        <form method="POST">
            <div class="form-group">
            <input type="submit" name="clearAll" value="Empty Cart"/>
            </form>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>




<?php
require(__DIR__ . "/../../partials/flash.php");
?>