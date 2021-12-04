<?php
require(__DIR__ . "/../../partials/nav.php");
if(!is_logged_in()){
    flash("You must log in to add to cart.");
    die(header("Location: login.php"));}

    $product_id = se($_GET, "id", -1, false);
    $user_id = $_SESSION["user"]["id"];
    $desired_quantity = 4;
    if(isset($_POST['desired_quantity'])){
        $desired_quantity = $_POST['desired_quantity'];
    }
    $result = [];
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM products where id =:id");
    try {
        $stmt->execute([":id" => $product_id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($r) {
            $result = $r;
        }
    } catch (PDOException $e) {
        flash("<pre>" . var_export($e, true) . "</pre>");
    }
    $unit_cost = $result["unit_price"];
    $product_name = $result["name"];
   //echo var_export($result, true);
    if (isset($_POST["submit"])) {
        $stmt = $db->prepare("INSERT INTO cart (product_id, user_id, desired_quantity, unit_cost) VALUES(:product_id, :user_id, :desired_quantity, :unit_cost) on DUPLICATE KEY UPDATE desired_quantity=desired_quantity + :desired_quantity");
        try {
            $stmt->execute([":product_id" => $product_id, ":user_id" => $user_id, ":desired_quantity" => $desired_quantity, ":unit_cost" => $unit_cost]);
            flash("Added to cart");
        } catch (Exception $e) {
            flash("<pre>" . var_export($e, true) . "</pre>");
        } 
    }
    
?>

<style>
    
</style>
<div class="container-fluid">
    <h1>Add <?php echo($product_name)?> To Cart</h1>
    <form method="POST">
        <div>
            <label for="desired_quantity">Quantity</label>
            <input type="number" min="1" name="desired_quantity" required />
        </div>
        <div>
        <input class="btn btn-primary" type="submit" value="Add to cart" name="submit" />
        

        </div>
    </form>

</div>
<?php
require_once(__DIR__ . "/../../partials/footer.php");
?>