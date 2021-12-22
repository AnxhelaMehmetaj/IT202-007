<?php
require(__DIR__ . "/../../partials/nav.php"); ?>
<?php

//php block have access to it
$productID = se($_GET, "id", -1, false);
$unit_price = 0;
$desired_quantity = 0;
$user_id = get_user_id();
$result = [];
$ratings = [];
$rate = [];

if (isset($_POST['desired_quantity'])) {
    $desired_quantity = $_POST['desired_quantity'];
}
//fetching

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

        $query = $db->prepare("SELECT AVG(rating) as Avgrate From Ratings where Ratings.product_id = :id");

        try {
            $r = $query->execute([":id" => $productID]);
            $rate = $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            flash("<pre>" . var_export($e, true) . "</pre>");
            error_log("rating lookup error: " . var_export($e, true));
        }

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

if (isset($_POST["submit"]) && isset($_POST["rating"]) && isset($_POST["comment"])) {
    $db = getDB();
    $stmt = $db->prepare("INSERT into Ratings (`product_id`, `user_id`, `rating`, `comment`)
     VALUES (:productID, :userID, :rating, :comment)");
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


<?php if (isset($result) && !empty($result)) : ?>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?php echo ($result["name"]); ?></h5>
            <div>Price: <?php echo ($result["unit_price"]); ?></div>
            <div>Quantity: <?php echo ($result["quantity"]); ?></div>
            <?php if ($result["quantity"] < 10) : ?>
                <div><?php echo ("Only " . $result["quantity"] . " left in stock, order soon."); ?></div>
            <?php endif; ?>
            <div>Description: <?php echo ($result["description"]); ?></div>
            </p>
            <?php if (is_logged_in()) : ?>
                <form method="POST">
                    <div class="form-group">
                        Quantity: <input type="number" min="0" name="desired_quantity" value="" />
                    </div>
                    <input type="submit" name="save" value="Add to Cart" />
                    <input type="hidden" name="price" value="<?php echo $result["unit_price"]; ?>" />
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>


<div class="card ">
    <h3><b>Rating & Reviews</b></h3>
    <div class="row">
        <form method="POST">
            <div class="group">
                <label>Leave a Review</label>
                <label for="rating">Rating:</label>
                <input type="text" name="comment" placeholder="Leave a Review" />
                <input type="range" id="rating" name="rating" min="1" max="5" step="1">
                <input type="submit" name="submit" value="Submit review" />
            </div>
        </form>
    </div>

</div>

</div>
<div class="col-md-6" style="margin-left: 7%">

    <?php if (isset($rate) && !empty($rate)) : ?>

        <?php foreach ($rate as $rating) : ?>
            <h3> Average rating: <?php echo $rating["Avgrate"]; ?></h3>
        <?php endforeach; ?>
    <?php endif; ?>



    <?php if (isset($ratings) && !empty($ratings)) : ?>
        <?php foreach ($ratings as $r) : ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"> User id: <?php echo ($r["user_id"]); ?></h5>
                    <div>Rating: <?php echo ($r["rating"]); ?></div>
                    <div>Comment: <?php echo ($r["comment"]); ?></div>
                </div>
             
            </div>
          
        <?php endforeach; ?>
    <?php endif; ?>

</div>
<?php include(__DIR__ . "/../../partials/pagination.php"); ?>


<?php
require(__DIR__ . "/../../partials/flash.php");
?>