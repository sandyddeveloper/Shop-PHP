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

if($role != 1){
    header("location: ../user/dashboard.php");
    exit();
}

//function to generate random string
function genRanStr($length = 10) {
    $characters = '123456789abcdefghjkmnopqrstwxyzABCDEFGHJKLMNPQRSTWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


$role = 0;
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit();
}

$id = $_SESSION["id"];
$username = $_SESSION["username"];
$role = intval(getUserData($id, "role"));


if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $act = mysqli_real_escape_string($conn, $_POST['act']);


    $acts = array("add", "edit", "del", "enable", "disable");
    if(!in_array($act, $acts)){
        exitWithError("Invalid action");
    }

    if($act == "add"){
        $code = mysqli_real_escape_string($conn, $_POST['code']);
        $discount = mysqli_real_escape_string($conn, $_POST['discount']);

        $sql = "INSERT INTO `coups` (`code`, `discount`) VALUES ('$code', '$discount')";
        $result = mysqli_query($conn, $sql);
        if($result){
            exitWithSuccess("Coupon added successfully");
        }else{
            exitWithError("Error adding coupon");

        }
    }
    elseif($act == "del"){
        $id = mysqli_real_escape_string($conn, $_POST['id']);

        $sql = "DELETE FROM `coups` WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        if($result){
            exitWithSuccess("Coupon deleted successfully");
        }else{
            exitWithError("Error deleting coupon");

        }
    }
    elseif($act == "edit"){
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $code = mysqli_real_escape_string($conn, $_POST['code']);
        $discount = mysqli_real_escape_string($conn, $_POST['discount']);
        $status = intval(mysqli_real_escape_string($conn, $_POST['status']));

        $sql = "UPDATE `coups` SET `code` = '$code' , `discount` = '$discount' , `status` = '$status' WHERE `id` = '$id'";

        $result = mysqli_query($conn, $sql);
        if($result){
            exitWithSuccess("Coupon updated successfully");
        }else{
            exitWithError("Error updating coupon");

        }
    }
    elseif($act == "enable"){
        $id = mysqli_real_escape_string($conn, $_POST['id']);

        $sql = "UPDATE `coups` SET `status` = '1' WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        if($result){
            exitWithSuccess("Coupon updated successfully");
        }else{
            exitWithError("Error updating coupon");

        }
    }

    elseif($act == "disable"){
        $id = mysqli_real_escape_string($conn, $_POST['id']);

        $sql = "UPDATE `coups` SET `status` = '0' WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        if($result){
            exitWithSuccess("Coupon updated successfully");
        }else{
            exitWithError("Error updating coupon");

        }
    }
}

?>


<!DOCTYPE html>
<html lang="en-US">

<head>
    <?php include(__DIR__ . '/../comp/header.php'); ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <title><?php echo $shop_title; ?> - Coupons Manager</title>
    <style>
         body {
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
                                    <h5>Coupons Manager</h5>
                                    <p>Manage your coupons here</p>
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
                            <form method="post" class="col-12">
                                <input type="hidden" name="act" value="add">
                                <div class="mb-3">
                                    <label for="name" class="form-label" style="color: #FFF !important;">Coupon:</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark" name="code" id="name" placeholder="Coupon name" required value="<?php echo genRanStr(10); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label" style="color: #FFF !important;">Discount percent:</label>
                                    <input type="number" class="form-control mb-3 text-white bg-dark" name="discount" id="name" placeholder="Discount" required value="0">
                                </div>
                                <button type="submit" class="btn btn-danger w-100">Submit</button>
                            </form>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="dashboard_main_head text-center">
                                    <h5>Existing Coupons</h5>
                                    <p>Manage your coupons here</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="dash_recent_order py-0 col-lg-12">
                                <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 border-0">ID</th>
                                            <th class="px-4 py-2 border-0">Coupon</th>
                                            <th class="px-4 py-2 border-0">Discount</th>
                                            <th class="px-4 py-2 border-0">Status</th>
                                            <th class="px-4 py-2 border-0">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $showlogs = mysqli_query($conn, "SELECT * FROM `coups` ORDER BY id DESC;");
                                        while($row = mysqli_fetch_array($showlogs)) { 
                                            $status = ($row['status'] == 1) ? '<span class="badge bg-success text-white">ON</span>' : '<span class="badge bg-danger text-white">OFF</span>';
                                            ?>
                                            <tr class="names" data-name="<?php echo $row['code']; ?>">
                                                <td class="px-4 py-2 border-0"><?php echo $row['id']; ?></td>
                                                <td class="px-4 py-2 border-0"><?php echo $row['code']; ?></td>
                                                <td class="px-4 py-2 border-0"><?php echo $row['discount']; ?></td>
                                                <td class="px-4 py-2 border-0"><?php echo $status; ?>
                                                    <?php
                                                    if($row['status'] == 1){
                                                        echo '<form method="post" style="display: inline;">
                                                                <input type="hidden" name="id" value="'.$row['id'].'">
                                                                <input type="hidden" name="act" value="disable">
                                                                <button type="submit" class="download-href"><i class="fas fa-times"></i></button>
                                                            </form>';
                                                    }else{
                                                        echo '<form method="post" style="display: inline;">
                                                                <input type="hidden" name="id" value="'.$row['id'].'">
                                                                <input type="hidden" name="act" value="enable">
                                                                <button type="submit" class="download-href"><i class="fas fa-check"></i></button>
                                                            </form>';
                                                    }
                                                    ?>
                                                </td>
                                                <td class="px-4 py-2 border-0">
                                                    <button class="download-href" data-bs-toggle="modal" data-bs-target="#modal<?php echo $row['id']; ?>" style="margin-right:10px;"><i class="fas fa-edit"></i></button>
                                                    <form method="post" style="display: inline;">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <input type="hidden" name="act" value="del">
                                                        <button type="submit" class="download-href"><i class="fas fa-trash-alt"></i></button>
                                                    </form>
                                                </td>
                                            </tr>

                                            <?php
                                            echo '<div class="modal fade" id="modal'.$row['id'].'" tabindex="-1" aria-labelledby="modal'.$row['id'].'" style="display: none;" aria-hidden="true">                
                                                <div class="modal-dialog" role="document">
                                                    <form method="post">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title text-white">Edit category</h5>
                                                            </div>
                                                            
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="col-form-label text-white" for="recipient-name">Code:</label>
                                                                    <input class="form-control mb-3 text-white bg-dark m-0 w-100" name="code" type="text" value="'.$row['code'].'">
                                                                    <input type="hidden" name="id" value="'.$row['id'].'">
                                                                    <input type="hidden" name="act" value="edit">
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="col-form-label text-white" for="recipient-name">Discount:</label>
                                                                    <input class="form-control mb-3 text-white bg-dark m-0 w-100" name="discount" type="text" value="'.$row['discount'].'">
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="col-form-label text-white" for="recipient-name">Title:</label>
                                                                    <select class="form-control mb-3 text-white bg-dark m-0 w-100" name="status">
                                                                        <option value="1" '.($row['status'] == 1 ? 'selected' : '').'>Active</option>
                                                                        <option value="0" '.($row['status'] == 0 ? 'selected' : '').'>Inactive</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal" data-bs-original-title="" title="">Close</button>
                                                                <button class="btn btn-primary" type="submit" id="btn27">Save</button>
                                                            </div>
                                                        </div>
                                                    </form> 
                                                    </div>
                                                </div>
                                            </div>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
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
        const search = document.getElementById("search");

        search.oninput = function() {
            var names = document.getElementsByClassName("names");

            for (var i = 0; i < names.length; i++) {
                names[i].style.display = "none";
            }

            let searchvalue = search.value.toLowerCase();

            for (var i = 0; i < names.length; i++) {
                name = names[i].dataset.name.toLowerCase();

                if (name.includes(searchvalue)) {
                    names[i].style.display = "table-row";
                }
            }
        };
    </script>
</body>
</html>
