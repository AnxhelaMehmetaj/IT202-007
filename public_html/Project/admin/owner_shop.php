<?php
require(__DIR__ . "/../../../partials/nav.php"); ?>
<?php

if (!has_role("Admin") OR has_role("Owner")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "home.php"));
}


$PER_PAGE = 10;
$results = [];
$outOfStock = "";
$current_page = 0;
if(isset($_GET["current_page"])){
    $current_page = $_GET["current_page"];
}

if(isset($_GET["outOfStock"])){
    if($_GET["outOfStock"] === 'true'){
        $outOfStock = "true";
    }
}



if(isset($_POST["save"])){
    if(isset($_POST["outOfStock"])){
        $outOfStock = "true";
    }
    else{
        $outOfStock = "";
    }
}
$params = [];
$query = "SELECT * FROM products WHERE 1=1";

if(!empty($outOfStock)){
    $query = $query . " AND stock <= 0";
}
$query .= " LIMIT " . $current_page * $PER_PAGE . ","  . $PER_PAGE;
$count_str = "SELECT COUNT(*) FROM " . explode('LIMIT', explode('FROM', $query)[1])[0]; //Circumcise the sql string in order to obtain count
$db = getDB();
$stmt = $db->prepare($query);
try {
    $stmt->execute($params);
    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($r) {
        $results = $r;
    }
} catch (PDOException $e) {
    flash("<pre>" . var_export($e, true) . "</pre>");
}
$stmt = $db->prepare($count_str);
try {
    $stmt->execute($params);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r) {
        $count_results = $r;
    }
} catch (PDOException $e) {
    flash("<pre>" . var_export($e, true) . "</pre>");
}

?>
<div class="container-fluid">
    <h1>Item List</h1>
    <form method="POST">
        <div>
            
            <input type="checkbox" name="outOfStock"/> Search items out of stock<br>
            <input type="submit" name="save" value="Submit"/>
        </div>
    </form>
    <div class="card">
    <?php
    if($current_page >= 1){
        echo("<a class='paginate_button' href = owner_shop.php?current_page=" . $current_page-1 .  "&outOfStock=" . $outOfStock . ">Previous</a>");
    }
    if(($current_page+1)*$PER_PAGE < $count_results["COUNT(*)"]){
        echo("<a class='paginate_button' href = owner_shop.php?current_page=" . $current_page+1 . "&itemName=" .  "&outOfStock=" . $outOfStock . ">Next</a>");
    }
    echo("</div>");
    

    ?>
    </div>
    <?php foreach ($results as $index => $r) : ?>

<div class='card'>
    <br>Name: <?php echo $r["name"] ?>
    <div><?php echo ($r["unit_price"]); ?></div>
    <div><?php echo ($r["stock"]); ?></div>
    <div><?php echo ($r["description"]); ?></div>

    <div><a type="button" href="view_products.php?id=<?php echo ($r['id']); ?>">View</a></div>
</div><br>

<?php endforeach; ?>


<?php
require(__DIR__ . "/../../../partials/footer.php");
?>