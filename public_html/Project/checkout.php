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
$results = [];
$subtotal = 0;
$address = "";
$paymentMethod = 0;
$hasError = true;
error_log("Loaded page");
$db = getDB();
$stmt = $db->prepare("SELECT cart.unit_price, name, product_id, cart.id, cart.desired_quantity From cart JOIN products on cart.product_id = products.id where cart.user_id=:user_id LIMIT 10");
$r = $stmt->execute([":user_id" => $userID,]);
if ($r) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    flash("There was a problem fetching the results " . var_export($stmt->errorInfo(), true));
}


if (isset($_POST["firstname"])) {
    if (empty($_POST["firstname"])) {
        $hasError = false;
        flash("There was a problem with Firstname");
    }
}
if (isset($_POST["email"])) {
    if (empty($_POST["email"])) {
        $hasError = true;
        flash("There was a problem with Email");
    }
}
if (isset($_POST["address"])) {
    if (empty($_POST["address"])) {
        $hasError = true;
        flash("There was a problem with Address ");
    }
}
if (isset($_POST["city"])) {
    if (empty($_POST["city"])) {
        $hasError = true;
        flash("There was a problem with city ");
    }
}
if (isset($_POST["state"])) {
    if (empty($_POST["state"])) {
        $hasError = true;
        flash("There was a problem with state");
    }
}
if (isset($_POST["zip"])) {
    if (empty($_POST["zip"])) {
        $hasError = true;
        flash("There was a problem with zip");
    }
}

if (isset($_POST["paymenttype"])) {
    if (empty($_POST["paymenttype"])) {
        $hasError = true;
        flash("There was a problem with payment type");
    }
}

if (!$hasError) { //don't need to check again if you swap how the boolean works
    // this is likely longer than your db column $address= $_POST["firstname"]. " " . $_POST["email"]. " ". $_POST["address"]. " ". $_POST["city"]. " ". $_POST["state"]. " ". $_POST["zip"];
    error_log("No error post: " . var_export($_POST, true));
    $address = $_POST["address"] . ", " . $_POST["city"] . ", " . $_POST["state"] . ", " . $_POST["zip"];
    $subtotal = $_POST["subtotal"];
    $paymentMethod = $_POST["paymenttype"];

    $stmt = $db->prepare("INSERT into orders(user_id, total_price, payment_method, address) VALUES (:userID, :price, :pmethod, :addr)");
    try {
        $r = $stmt->execute([
            ":userID" => $userID,
            ":addr" => $address,
            ":pmethod" => $paymentMethod,
            ":price" => $subtotal
        ]);
    } catch (PDOException $e) {
        error_log("Error saving order: " . var_export($e, true));
    }
} else {
    error_log("Had some error");
}


?>

<div class="row">
  <div class="col-75">
    <div class="container">
      <form method="$_POST">
      
        <div class="row">
          <div class="col-50">
            <h3>Billing Address</h3>
            <label for="fname"><i class="fa fa-user"></i> Full Name</label>
            <input type="text" id="fname" name="firstname" placeholder="John M. Doe">
            <label for="email"><i class="fa fa-envelope"></i> Email</label>
            <input type="text" id="email" name="email" placeholder="john@example.com">
            <label for="adr"><i class="fa fa-address-card-o"></i> Address</label>
            <input type="text" id="adr" name="address" placeholder="542 W. 15th Street">
            <label for="city"><i class="fa fa-institution"></i> City</label>
            <input type="text" id="city" name="city" placeholder="New York">

            <div class="row">
              <div class="col-50">
                <label for="state">State</label>
                <input type="text" id="state" name="state" placeholder="NY">
              </div>
              <div class="col-50">
                <label for="zip">Zip</label>
                <input type="text" id="zip" name="zip" placeholder="10001">
              </div>
            </div>
          </div>
</form>
    
        <div class="col-50">
            <h3>Payment</h3>
            <label for="fname">Accepted Cards</label>
            <div class="icon-container">
              <i class="fa fa-cc-visa" style="color:navy;"></i>
              <i class="fa fa-cc-amex" style="color:blue;"></i>
              <i class="fa fa-cc-mastercard" style="color:red;"></i>
              <i class="fa fa-cc-discover" style="color:orange;"></i>
            </div>
          <label for="cname">Name on Card</label>
            <input type="text" id="cname" name="cardname" placeholder="John More Doe">
            <label for="ccnum">Credit card number</label>
            <input type="text" id="ccnum" name="paymenttype" placeholder="1111-2222-3333-4444">
            <label for="expmonth">Exp Month</label>
            <input type="text" id="expmonth" name="expmonth" placeholder="September">
            <div class="row">
              <div class="col-50">
                <label for="expyear">Exp Year</label>
                <input type="text" id="expyear" name="expyear" placeholder="2018">
              </div>
              <div class="col-50">
                <label for="cvv">CVV</label>
                <input type="text" id="cvv" name="cvv" placeholder="352">
              </div>
            </div> 
          </div>
          
        
        
        <input type="submit"  name= "save" value="Continue to checkout" class="btn">
    </div>  
    </div>
  </div>
  <div class="col-25">
    <div class="container">
      <h4>Cart <span class="price" style="color:black"><i class="fa fa-shopping-cart"></i> <b></b></span></h4>
     
    <?php if (count($results) > 0): ?>
            <?php foreach ($results as $r): ?>
               <?php  $subtotal += ($r["unit_price"]*$r["desired_quantity"]); ?>
               
      
      <div class="card" style="width: 18rem;">
      <div class="card-body">
      
      <p>Name:</a> <span class="price"> <?php echo($r["name"]); ?> </span></p>
      <p>Price: <span class="price"><?php echo($r["unit_price"]); ?></span></p>
      <p>Quantity <span class="price"><?php echo($r["desired_quantity"]); ?></span></p>
       <hr>
    </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
            
      <h2>Total <span class="price" style="color:black"><b><?php echo($subtotal); ?></b></span></h2>
    </div>
  </div>
</div>



<?php
require(__DIR__ . "/../../partials/flash.php");
?>