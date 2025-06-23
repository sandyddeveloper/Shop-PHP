<?php

session_start();

require_once(__DIR__ . '/../server/db.php');
require_once(__DIR__ . '/../comp/functions.php');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit();
}

$id = $_SESSION["id"];
$username = $_SESSION["username"];
$role = intval(getUserData($id, "role"));

if($role != 1 && $role != 3){
    header("location: ../user/dashboard.php");
    exit();
}

if(!isset($_GET['id'])){
    $error = "Invalid product";
    $error = urlencode($error);
    header("location: admin_prod.php?error=".$error);
    exit();
}

$prod_id = mysqli_real_escape_string($conn, $_GET['id']);
$sql = "SELECT * FROM `prods` WHERE `id` = '$prod_id'";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) == 0){
    $error = "Invalid product";
    $error = urlencode($error);
    header("location: admin_prod.php?error=".$error);
    exit();
}

$prod = mysqli_fetch_assoc($result);

// get stock
$sql = "SELECT * FROM `stock` WHERE `prod_id` = '$prod_id'";
$result = mysqli_query($conn, $sql);
$stock = mysqli_num_rows($result);

$sql21 = "SELECT * FROM `stock` WHERE `prod_id` = '$prod_id'";
$result = mysqli_query($conn, $sql21);
$currentStockCodes = array();
while ($row = mysqli_fetch_assoc($result)) {
    $currentStockCodes[] = $row['code'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $arr = explode("\r\n", trim($_POST['codes']));

    $sql2 = "DELETE FROM `stock` WHERE `prod_id` = '$prod_id'";
    $result2 = mysqli_query($conn, $sql2);

    $sql = "INSERT INTO `stock` (`prod_id`, `code`) VALUES ";
    foreach ($arr as $line) {
        $line = mysqli_real_escape_string($conn, $line);
        if($line != ""){
            $sql .= "('$prod_id', '$line'),";
        }
    }
    $sql = rtrim($sql, ",");
    $result = mysqli_query($conn, $sql);
    if($result){
        $success = "Stock updated successfully";
        $success = urlencode($success);
        header("location: add_stock.php?id=".$prod_id."&success=".$success);
        exit();
    }else{
        $error = "Something went wrong";
        $error = urlencode($error);
        header("location: add_stock.php?id=".$prod_id."&error=".$error);
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en-US">

<head>
    <?php include(__DIR__ . '/../comp/header.php'); ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <title><?php echo $shop_title; ?> - Stock Manager</title>
    <style>
        .drop-zone {
    border: 2px dashed #ccc;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
}

.drop-zone--over {
    background-color: #f0f0f0;
}

.drop-zone__prompt {
    font-size: 16px;
    color: #666;
}

.drop-zone__input {
    display: none;
}


body{
overflow-x: hidden;
}

    </style>
</head>

<body>

   <main class="main" id="top">
    <?php include(__DIR__ . '/../comp/nav.php'); ?>
    <div class="dashboard_main_sec">
        <div class="container">
            <div class="row">
                <?php include(__DIR__ . '/../comp/subnav.php'); ?>
                <div class="col-lg-9">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="dashboard_main_head text-center">
                                    <h5>Stock Manager</h5>
                                    <p>Manage stock</p>
                                </div>
                            </div>
                        </div>

                        <?php  
                        if(isset($_GET['error'])){
                            $error = htmlspecialchars(urldecode($_GET['error']));
                            echo '<div class="alert alert-danger" role="alert">';
                            echo $error;
                            echo '</div>';
                        } elseif(isset($_GET['success'])){
                            $success = htmlspecialchars(urldecode($_GET['success']));
                            echo '<div class="alert alert-success" role="alert">';
                            echo $success;
                            echo '</div>';
                        }
                        ?>

                        <div class="row mt-2 mb-5">
                            <form class="col-lg-12" method="POST" action="add_stock.php?id=<?php echo $prod_id; ?>">
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="mb-3">
                                            <span class="fa fa-truck me-3 text-primary"></span>
                                            <span class="text-white">Current Stock: <?php echo $stock; ?></span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <textarea class="form-control bg-dark text-white" rows="10" name="codes"><?php
                                                foreach ($currentStockCodes as $code) {
                                                    echo $code . "\n";
                                                }
                                            ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex justify-content-between">
                                    <div>
                                        <!-- Delete Button -->
                                        <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete All Stock</button>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-danger w-100">Save</button>
                                    </div>
                                </div>
                            </form>
                                     <div class="row mt-3 text-center">
    <div class="col-md-12">
        <label class="form-label text-white" style="margin-left: 450px">Upload Stock from Text File</label>
        <div class="drop-zone d-flex align-items-center justify-content-center" id="dropZone" style="margin-left: 450px">
            <span class="drop-zone__prompt">Drag & Drop a text file here or click to select</span>
            <input type="file" name="fileInput" class="drop-zone__input" accept=".txt">
        </div>
    </div>
</div>


                        </div>
                    </div>
          

                </div>
            </div>
        </div>
    </main>

    <?php include(__DIR__ . '/../comp/footer.php'); ?>

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/script.js"></script>
<script>
    document.getElementById('dropZone').addEventListener('dragover', (event) => {
        event.preventDefault();
        document.getElementById('dropZone').classList.add('drop-zone--over');
    });

    document.getElementById('dropZone').addEventListener('dragleave', () => {
        document.getElementById('dropZone').classList.remove('drop-zone--over');
    });

    document.getElementById('dropZone').addEventListener('drop', (event) => {
        event.preventDefault();
        document.getElementById('dropZone').classList.remove('drop-zone--over');

        const fileInput = document.querySelector('input[name="fileInput"]');
        const files = event.dataTransfer.files;

        if (files.length > 0) {
            const reader = new FileReader();

            reader.onload = function (e) {
                const fileContent = e.target.result;
                document.querySelector('textarea[name="codes"]').value = fileContent;
            };

            reader.readAsText(files[0]);
        }
    });
</script>

    <script>
    
    
        function confirmDelete() {
            if (confirm("Are you sure you want to delete all stock?")) {
                // Redirect to delete action
                window.location.href = "add_stock.php?id=<?php echo $prod_id; ?>&delete=true";
            }
        }
    </script>

    <?php
        // Handle delete action
        if(isset($_GET['delete']) && $_GET['delete'] == 'true') {
            $sql_delete = "DELETE FROM `stock` WHERE `prod_id` = '$prod_id'";
            mysqli_query($conn, $sql_delete);
            echo '<script>alert("All stock deleted successfully");</script>';
        }
    ?>

</body>
</html>
