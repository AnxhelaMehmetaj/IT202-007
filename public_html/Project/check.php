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
$pquantity = 0;
$subtotal = 0;
$payments = [];
$paymentMethod ="";
$address = "";
$amountTendered = 0;
$noError = True;
$query = "";
$orderID =0;
$redirect = "";
$backupOrderID = 0;






$db = getDB();
    $stmt = $db->prepare("SELECT cart.unit_price, name, product_id, cart.id, cart.desired_quantity as pquantity From cart JOIN products on cart.product_id = products.id where cart.user_id=:user_id LIMIT 10");
    $r = $stmt->execute([":user_id"=> $userID,]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results " . var_export($stmt->errorInfo(), true));
    }
    
    if (isset($_POST["payment"])){
        $subtotal = $_POST["subtotal"];
        if (empty($_POST["payment"])){
            flash("Please enter a payment value higher than your subtotal");
            $noError = false;
        }
        if ($_POST["payment"] < $subtotal) {
        $noError = false;   
        flash("Amount Tendered Lower than subtotal");
       }   }



    if (isset($_POST["streetLine1"])){
    if (empty($_POST["streetLine1"])){
    $noError = false;   
    flash("There was a problem with Street Line 1");
   } }

  
   if (isset($_POST["payment_method"])){
    if ($_POST["payment_method"] == "-1"){
    $noError = false;   
    flash("Please Select a Payment Method");
   } }  

   if (isset($_POST["steetLine2"])){
   if (empty($_POST["streetLine2"])){
    $noError = false;  
    flash("Please Enter a Payment Method");
} }

if (isset($_POST["state"])){
if (empty($_POST["state"])){
    $noError = false;  
    flash("There was a problem with State");
} }


if (isset($_POST["city"])){
if (empty($_POST["city"])){
    $noError = false;  
    flash("There was a problem with City");
} }

if (isset($_POST["zipCode"])){
if (empty($_POST["zipCode"])){
    $noError = false;  
    flash("There was a problem with Zip Code");
} }







if(isset($_POST["quantity"])) {
    $quantity = (int)$_POST["quantity"];
    if($quantity == 0) {
        $cartID = $_POST["id"];
        $db = getDB();
        $stmt = $db->prepare("DELETE From Cart where id = :cartID");
        $r = $stmt->execute([":cartID"=> $cartID,]);
    }
    if ($quantity != 0 ) {
        $productID = $_POST["product_id"];
        $db = getDB();
        $stmt = $db->prepare("INSERT into cart (`product_id`, `user_id`, `desired_quantity`) VALUES (:productID, :userID, :quantity) on duplicate key update quantity = :desired_quantity");
        $r = $stmt->execute([
            ":productID" => $productID,
            ":userID" => $userID,
            ":desired_quantity" => $quantity
            ]);
}
}
    
    if ($noError == True && !empty($_POST["streetLine1"]) && !empty($_POST["streetLine2"]) && !empty($_POST["city"]) && !empty($_POST["state"]) && !empty($_POST["zipCode"])) {
        $quantity = $_POST["cquantity"]; 
        $pquantity = $_POST["pquantity"];
        $address = $_POST["streetLine1"] . " " . $_POST["streetLine2"] . " " . $_POST["city"] . " " . $_POST["state"] . " " . $_POST["zipCode"] ;
        $paymentMethod = $_POST["payment_method"];
        $subtotal = $_POST["subtotal"];
        $amountTendered = $_POST["payment"];
        $newQuantity = $pquantity - $quantity;

        
        
        $stmt = $db->prepare("INSERT into Orders (`user_id`, `total_price`, `payment_method`, `address`) VALUES (:userID, :tprice, :pmethod, :addr)");
        $r = $stmt->execute([
        ":tprice"=>$subtotal,
        ":userID"=>$userID,
        ":pmethod"=>$paymentMethod,
        ":addr"=>$address
        ]);
        $db = getDB();
        $orderID = $db->lastInsertId();
        $backupOrderID = $orderID;

        echo var_export($stmt->errorInfo(), true);

        $query = "INSERT into OrderItems (`user_id`, `unit_price`, `product_id`, `order_id`, `quantity`) VALUES ";
                $params = [];
                foreach ($results as $index => $result) {
                    if ($index > 0) {
                        $query .= ",";
                    }
                    $query .= "(:userID, :price$index, :pid$index, :oid, :quantity$index )";
                    $params[":pid$index"] = $result["product_id"];
                    $params[":quantity$index"] = $result["quantity"];
                    $params[":price$index"] = $result["unit_price"];
                   }
           $params[":oid"] = $orderID;
           $params[":userID"] = $userID;

        $stmt = $db->prepare($query);
        $r = $stmt->execute($params);
        echo var_export($stmt->errorInfo(), true);
        $db = getDB();
        $stmt = $db->prepare("DELETE from Cart where user_id = :userID");
        $r = $stmt->execute([":userID"=> $userID,]);
        $db = getDB();
        foreach ($results as $index => $result) {
        $stmt = $db->prepare("UPDATE Products SET quantity = :newQuantity WHERE id = :productID");
        $r = $stmt->execute([
            ":newQuantity"=>$newQuantity,
            ":productID"=>$result["product_id"]
        ]);  }
       
        echo var_export($stmt->errorInfo(), true);
        $redirect = "Location: view_order.php?id=";
        $redirect .= $backupOrderID;
        header($redirect);
                }
?>


<h3>Checkout</h3>
<div class="results">
    <?php if (count($results) > 0): ?>
            <?php foreach ($results as $r): ?>
               <?php  $subtotal += ($r["unit_price"]*$r["desired_quantity"]); ?>
                <div class="card" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title"><?php echo($r["name"]); ?></h5>
                <div>Price: <?php echo(($r["unit_price"]* $r["desired_quantity"])); ?></div>
                </div>
                <form method="POST">
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" name="quantity" value="<?php echo $r["desired_quantity"]; ?>"/>
                    <?php if ($r["pquantity"] < $r["desired_quantity"]) : ?>
                    <?php flash("Quantity of " . $r["name"] . " too high, please enter a lower quantity");
                    $noError = False; 
                    endif; ?>
                     <?php if ($r["pquantity"] > $r["desired_quantity"]): ?>
                    <?php $noError = True; ?>
                <?php endif; ?>
                    <input type="submit" name="save" value="Update Quantity"/>
                    <input type="hidden" name="product_id" value="<?php echo $r["product_id"]; ?>"/>
                    <input type="hidden" name="id" value="<?php echo $r["id"]; ?>"/>
                </div>
                </form>
                <form method="POST">
                    <input type="hidden" name="quantity" value="0"/>
                    <input type="submit" value="Remove Item"/>
                    <input type="hidden" name="product_id" value="<?php echo $r["product_id"]; ?>"/>
                    <input type="hidden" name="id" value="<?php echo $r["id"]; ?>"/>        
                </form>
            
            <?php endforeach; ?>
           <div>    
            <a type="button" href="checkout.php">Checkout</a>
            </div>
        </div>
        
        
        
        
        <div class="card" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title">Subtotal:<?php echo($subtotal); ?></h5>
        </div> </div> </div>


<label>Payment Method</label>
        <form method="POST"> 
            <select name="payment_method" value="<?php echo $payment["payment_method"];?>" >
                <option value="-1">None</option>
                <?php foreach ($payments as $payment): ?>
                    <option value="<?php echo($payment["payment_method"]); ?>" 
                    ><?php echo($payment["payment_method"]); ?></option> <?php echo ($payment["payment_method"] == $product["id"] ? 'selected="selected"' : ''); ?>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
        


        <input type="number" name="payment" value=""/>
        <input type="submit" name="payment" value="Payment"/>


        <input type="text" name="streetLine1" placeholder="Street Line 1"/>
        <input type="text" name="streetLine2" placeholder="Street Line 2"/>
        <input type="text" name="city" placeholder="City"/>
        <input type="text" name="state" placeholder="State"/>
        <input type="number" name="zipCode" placeholder="Zip Code"/>
        <input type="hidden" name="subtotal" value="<?php echo $subtotal; ?>"/>
        <input type="hidden" name="cquantity" value="<?php echo $r["desired_quantity"]; ?>"/>
        <input type="hidden" name="pquantity" value="<?php echo $r["pquantity"]; ?>"/>
        <input type="submit" name="save" value="Place Order"/>
</form>

<?php
require(__DIR__ . "/../../partials/flash.php");
?>