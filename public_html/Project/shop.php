<?php
require(__DIR__ . "/../../partials/nav.php");

$results = [];
$db = getDB();
$outofstock = '';
//Sort and Filters
$col = se($_GET, "col", "unit_price", false);
//allowed list
if (!in_array($col, ["unit_price", "stock", "name", "created", "outofstock"])) {
    $col = "unit_price"; //default value, prevent sql injection
}
$order = se($_GET, "order", "asc", false);
//allowed list
if (!in_array($order, ["asc", "desc"])) {
    $order = "asc"; //default value, prevent sql injection
}

$name = se($_GET, "name", "", false);

//split query into data and total
$base_query = "SELECT id, name, description, unit_price, stock, image FROM products  ";
$total_query = "SELECT count(1) as total FROM products";
//dynamic query
$query = " WHERE 1=1"; //1=1 shortcut to conditionally build AND clauses
$params = []; //define default params, add keys as needed and pass to execute
//apply name filter
if (!empty($name)) {
    $query .= " AND name like :name";
    $params[":name"] = "%$name%";
}

//apply column and order sort
if (!empty($col) && !empty($order)) {
    $query .= " ORDER BY $col $order"; //be sure you trust these values, I validate via the in_array checks above
}
//paginate function
$per_page = 3;
paginate($total_query . $query, $params, $per_page);
$query .= " LIMIT :offset, :count";
$params[":offset"] = $offset;
$params[":count"] = $per_page;
//get the records
$stmt = $db->prepare($base_query . $query); //dynamically generated query
//we'll want to convert this to use bindValue so ensure they're integers so lets map our array
foreach ($params as $key => $value) {
    $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($key, $value, $type);
}
$params = null; //set it to null to avoid issues
try {
    $stmt->execute($params); //dynamically populated params to bind
    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($r) {
        $results = $r;
    }
} catch (PDOException $e) {
    flash("<pre>" . var_export($e, true) . "</pre>");
}
?>


<div class="container-fluid">
    <h1>Shop</h1>
    <form class="row row-cols-auto g-3 align-items-center" style="width:fit-content">
        <div class="col" style="width:fit-content">
            <div class="input-group" style="width:fit-content">
                <div class="input-group-text">Name</div>
                <input class="form-control" name="name" value="<?php se($name); ?>" />
            </div>
        </div>
        <div class="col" style="width:fit-content">
            <div class="input-group" style="width:fit-content">
                <div class="input-group-text" style="width:fit-content">Sort</div>
                <!-- make sure these match the in_array filter above-->

                <select class="form-control" name="col" value=" ?>">
                    <option value="unit_price">Unit Price </option>
                    <option value="stock">Stock</option>
                    <option value="name">Name</option>
                    <option value="created">Created</option>
                

                </select>
                <script>
                    //quick fix to ensure proper value is selected since
                    //value setting only works after the options are defined and php has the value set prior
                    document.forms[0].col.value = "<?php se($col); ?>";
                </script>
                <select class="form-control" name="order" value="<?php se($order); ?>">
                    <option value="asc">Up</option>
                    <option value="desc">Down</option>
                </select>
                <script>
                    //quick fix to ensure proper value is selected since
                    //value setting only works after the options are defined and php has the value set prior
                    document.forms[0].order.value = "<?php se($order); ?>";
                </script>
            </div>
           
        </div>
        <div class="col">
            <div class="input">
                <input type="submit" class="btn btn-primary" value="Apply" />
            </div>
        </div>
    </form>

    <div class="row row-cols-1 row-cols-md-5 g-4">
        <?php foreach ($results as $r) : ?>

            <div class="card">
                <div>
                    <div>Name:</div>
                    <div><?php echo ($r["name"]); ?></div>
                </div>
                <div>
                    <div>Price:</div>
                    <div><?php echo ($r["unit_price"]); ?></div>
                </div>
                <div>
                    <div>Quantity:</div>
                    <div><?php echo ($r["stock"]); ?></div>
                </div>
                <div>
                    <div>Description:</div>
                    <div><?php echo ($r["description"]); ?></div>
                </div>
                <div>

                    <a type="button" href="view_products.php?id=<?php echo ($r['id']); ?>">View</a>
                </div>
            </div>

        <?php endforeach; ?>
    </div>
    <!-- this will be moved into a partial file for reusability-->
    <?php include(__DIR__ . "/../../partials/pagination.php"); ?>
</div>
<?php
require(__DIR__ . "/../../partials/footer.php");
?>