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
//fetching
$result = [];
$ratings = [];
$rating =[];
if (isset($productID) && $productID > 0) {
    $db = getDB();
    $stmt = $db->prepare("SELECT name, unit_price, quantity, description, id FROM products where id = :id");

    try {
        $r = $stmt->execute([":id" => $productID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        flash("<pre>" . var_export($e, true) . "</pre>");
        error_log("product lookup error: " . var_export($e, true));
    }
    if ($result) {

        $db = getDB();
        $stmt = $db->prepare("SELECT user_id, rating, comment,  Users.id FROM Ratings JOIN Users on Ratings.user_id = Users.id
     where Ratings.product_id = :id");
  
        try {
            $r = $stmt->execute([":id" => $productID]);
            $ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            flash("<pre>" . var_export($e, true) . "</pre>");
            error_log("rating lookup error: " . var_export($e, true));
        }
    
    $query=$db->prepare("SELECT AVG(rating) as Avgrate From Ratings where Ratings.product_id = :id");

    try {
        $r = $query->execute([":id" => $productID]);
        $rating = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        flash("<pre>" . var_export($e, true) . "</pre>");
        error_log("rating lookup error: " . var_export($e, true));
    } 
} } else {
    flash("Invalid product");
}

if (isset($_POST["save"]) && isset($_POST["rating"]) && isset($_POST["comment"])) {
    $db = getDB();
    $stmt = $db->prepare("INSERT into Ratings (`product_id`, `user_id`, `rating`, `comment`) VALUES (:productID, :userID, :rating, :comment)");
    try {
        $r = $stmt->execute([
            ":productID" => $productID,
            ":userID" => $user_id,
            ":comment" => $_POST["comment"],
            ":rating" => $_POST["rating"]
        ]);
        $ratings = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        flash("<pre>" . var_export($e, true) . "</pre>");
        error_log("rating error: " . var_export($e, true));
    }

    
}

?>


<?php if (isset($result) && !empty($result) && isset($ratings)): ?>
    <div class="card" style="width: 20rem; ">
        <div class="card-body">
        <h5 class="card-title"><?php echo($result["name"]); ?></h5>
            
        <div>Price: <?php echo($result["unit_price"]); ?></div>
                <div>Quantity: <?php echo($result["quantity"]); ?></div>
                <?php if ($result["quantity"] < 10): ?>   
                    <div><?php echo("Only " . $result["quantity"] . " left in stock, order soon."); ?></div>
                <?php endif;?>
                <div>Description: <?php echo($result["description"]); ?></div>
            
                <?php  if (is_logged_in()): ?>
                <form method="POST">
                    <div class="form-group">
                       Quantity: <input type="number" min="0" name="desired_quantity" value=""/>
                    </div>
                    <input type="submit" name="save" value="Add to Cart"/>
                    <input type="hidden" name="price" value="<?php echo $result["unit_price"]; ?>"/>
                </form>
              
                
                    
                <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="row container">
<div class="col-md-4 ">
	<h3><b>Rating & Reviews</b></h3>
	<div class="row">
    <form method ="POST">
        <div class="form-group">
            <label>Leave a Review</label>
            <label for="rating">Rating:</label>
            <input type="text" name="comment" placeholder="Leave a Review"/>
            <input type="range" id="rating" name="rating" min="1" max="5" step="1">   
            <input type="submit" name="save" value="Submit review"/> 
        </div>
    </form>
    <?php if (isset($rating) && !empty($rating) ): ?>

<?php foreach ($rating as $r): ?>
<h3 > Average rating: <?php echo ($r["Avgrate"]);?></h3>
<?php endforeach; ?>
<?php endif; ?>

    </div>

   

    </div>
    <?php if (isset($ratings) && !empty($ratings) ): ?>
     
                        <?php foreach ($ratings as $r): ?>
                            <div class="card" style="width: 20rem; ">
                            <div class="card-body">
                            
                        <h5 class="card-title"> User id: <?php echo($r["user_id"]); ?></h5>
                        <div>Rating: <?php echo($r["rating"]); ?></div>
                        <div>Comment: <?php echo($r["comment"]); ?></div>
                            </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
    </div>
</div>




<?php
require(__DIR__ . "/../../partials/flash.php");
?>
