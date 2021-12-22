<?php
require(__DIR__ . "/../../partials/nav.php"); ?>

<?php
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
?>
<?php
$PER_PAGE = 10;
$currentpage = 0;
$total_price = false;
$by_date = false;
$start = '';
$end = '';

if (isset($_GET["by_date"])) {
    $by_date = true;
}
if (isset($_GET["currentpage"])) {
    $currentpage = $_GET["currentpage"];
}

if (isset($_GET["start"])) {
    $start = $_GET["start"];
}
if (isset($_GET["end"])) {
    $end = $_GET["end"];
}
if (isset($_GET["total_price"])) {
    $total_price = $_GET["total_price"];
}
//Update values with submit
if (isset($_POST['save'])) {
    if (isset($_POST["total_price"])) {
        $total_price = true;
    } else {
        $total_price = false;

        if (isset($_POST["by_date"])) {
            $by_date = true;
        }
    }
    if ($_POST["start"] !== '') {
        $start = date($_POST["start"] . " 00:00:00");
    } else {
        $start = '';
    }
    if ($_POST["end"] !== '') {
        $end = date($_POST["end"] . " 23:59:59");
    } else {
        $end = '';
    }
}


$db = getDB();
$results = [];
$query = "";
$count_str=[];
if (has_role("Owner")) {
    $query = "SELECT * FROM orders WHERE user_id = :user_id OR NOT user_id = :user_id ";
} else {
    $query = "SELECT * FROM orders WHERE user_id = :user_id ";
}

if ($start !== '') {
    $query = $query . "AND created >= '" . $start . "' ";
}
if ($end !== '') {
    $query = $query . "AND created <= '" . $end . "' ";
}
if ($total_price) {
    $query = $query . "ORDER BY total_price DESC";
} else if ($by_date) {
    $query = $query . "ORDER BY created DESC";
}


$query = $query . " LIMIT "  . $currentpage * $PER_PAGE . "," . $PER_PAGE . " ";
$count_str = "SELECT COUNT(*) FROM " . explode('LIMIT', explode('FROM', $query)[1])[0]; //Circumcise the sql string in order to obtain count


$stmt = $db->prepare($query);
try {
    $stmt->execute([":user_id" =>$_SESSION["user"]["id"]]);
    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($r) {
        $results = $r;
    }
} catch (PDOException $e) {
    flash("<pre>" . var_export($e, true) . "</pre>");
} 

$stmt = $db->prepare($count_str);
try {
    $stmt->execute([":user_id" => $_SESSION["user"]["id"]]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r) {
        $count_results = $r;
    }
} catch (PDOException $e) {
    flash("<pre>" . var_export($e, true) . "</pre>");
}




?>



<h1>Order History <?php echo($currentpage + 1)?></h1>
<div class="col">
<?php if($currentpage >= 1){
    echo("<a class='paginate_button' href = list_order.php?total_price=" . $total_price . "&currentpage=" . $currentpage - 1 . "&start=" . $start . "&end=" . $end . "&by_date=" . $by_date . ">Previous</a>");
}
if(($currentpage+1)*$PER_PAGE < $count_results["COUNT(*)"]){
    echo("<a class='paginate_button' href = list_order.php?total_price=" . $total_price . "&currentpage=" . $currentpage + 1 . "&start=" . $start . "&end=" . $end . "&by_date=" . $by_date . ">Next</a>");
} ?>
</div>

<div class="card" style="width: 80%; height:fit-content;">
    <form method="POST">
        <label>Sort by Price</label>
        <input class="form-control" name="total_price" value="Price"></input><br>
        <label>Sort by date</label>
        <input type='form-control' value='by_date' /><br>
        <label>Start</label>
        <input type='date' name='start' /><br>
        <label>End</label>
        <input type='date' name='end' /><br>
        <input type='submit' name='save' value='submit' /><br>
    </form>
</div>

<?php
foreach ($results as $index => $value) : ?>

    <div class='card'>
        <br>Order <?php echo $value["id"] ?>
        <div> Date and time <?php echo $value["created"] ?> </div>
        Total price: <?php echo $value["total_price"] ?>
        <div>Payment Method: <?php echo $value["payment_method"] ?> </div>
        Address: <?php echo $value["address"] ?>
        <div><a href="view_order.php?id= <?php echo $value["id"] ?> ">Order Info</a> </div>
    </div><br>

<?php endforeach; ?>
<?php
require(__DIR__ . "/../../partials/flash.php");
?>