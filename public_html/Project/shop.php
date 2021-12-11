<?php
require(__DIR__ . "/../../partials/nav.php");

$query = "";
if (isset($_POST["query"])) {
    $query = $_POST["query"]; }
$results = [];
$db = getDB();
$stmt = $db->prepare("SELECT id, name, description, unit_price, quantity, stock, image FROM products WHERE name like :q LIMIT 10");
try {
    $stmt->execute([":q" => "%$query%"]);
    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($r) {
        $results = $r;
    }
} catch (PDOException $e) {
    flash("<pre>" . var_export($e, true) . "</pre>");
}




?>


<h3>List Products</h3>
<form method="POST">
    <div class="form-group">    
        <input name="query" placeholder="Search" value="<?php echo($query); ?>"/>
        <input type="submit" value="Search" name="search"/>
    </div>
</form>
<div class="results">
    <?php if (count($results) > 0): ?>
        <div class="list-group">
            <?php foreach ($results as $r): ?>
                <div class="list-group-item">
                    <div>
                        <div>Name:</div>
                        <div><?php echo($r["name"]); ?></div>
                    </div>
                    <div>
                        <div>Price:</div>
                        <div><?php echo($r["unit_price"]); ?></div>
                    </div>
                    <div>
                        <div>Quantity:</div>
                        <div><?php echo($r["quantity"]); ?></div>
                    </div>
                    <div>
                        <div>Description:</div>
                        <div><?php echo($r["description"]); ?></div>
                    </div>
                    <div>
                       
                        <a type="button" href="view_products.php?id=<?php echo($r['id']); ?>">View</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>