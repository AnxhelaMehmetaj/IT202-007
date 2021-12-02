<?php
require(__DIR__ . "/../../partials/nav.php");


?>



   
    <?php 
    if(isset($_GET['id']))
    {
    $pid = $_GET['id'];
    $results = [];
    $db = getDB();
        $stmt = $db->prepare("SELECT * from products where id=$pid");
        try {
            $stmt->execute();
            $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($r) {
                $results = $r;
            }
        } catch (PDOException $e) {
            flash("<pre>" . var_export($e, true) . "</pre>");
        }
    
        
  
   
}
    ?>
 

<div class="container-fluid">
    <h1>Shop</h1>

    <div class="row row-cols-1 row-cols-md-5 g-4">
        <?php foreach ($results as $item) : ?>
            <div class="col">
                <div class="card bg-dark">
                    <div class="card-header">
                        Description
                    </div>
                    <?php if (se($item, "image", "", false)) : ?>
                        <img src="<?php se($item, "image"); ?>" class="card-img-top" alt="...">
                    <?php endif; ?>

                    <div class="card-body">
                        <h5 class="card-title">Name: <?php se($item, "name"); ?></h5>
                        <p class="card-text">Description: <?php se($item, "description"); ?></p>
                        
                    </div>
                    
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php
require(__DIR__ . "/../../partials/footer.php");
?>