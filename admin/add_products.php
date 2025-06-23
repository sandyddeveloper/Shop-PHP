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


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $act = filter_input(INPUT_POST, 'act', FILTER_SANITIZE_STRING);

    $acts = ["add", "edit", "del", "duplicate"];
    if (!in_array($act, $acts)) {
        exitWithError("Invalid action");
    }

    if ($act == "add") {
        // Prepare statement for ADD action
        $stmt = $conn->prepare("INSERT INTO `prods` (`title`, `dsc`, `price`, `cat_id`, `img`, `hide`, `priority`) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdisii", $_POST['title'], $_POST['dsc'], $_POST['price'], $_POST['cat'], $_POST['img'], $_POST['hide'], $_POST['priority']);
        if ($stmt->execute()) {
            exitWithSuccess("Product added successfully");
        } else {
            exitWithError("Error adding product: " . $stmt->error);
        }
    } elseif ($act == "del") {
        // Prepare statement for DELETE action
        $stmt = $conn->prepare("DELETE FROM `prods` WHERE `id` = ?");
        $stmt->bind_param("i", $_POST['id']);
        if ($stmt->execute()) {
            exitWithSuccess("Product deleted successfully");
        } else {
            exitWithError("Error deleting product: " . $stmt->error);
        }
    } elseif ($act == "edit") {
        // Prepare statement for EDIT action
        $stmt = $conn->prepare("UPDATE `prods` SET `title` = ?, `dsc` = ?, `price` = ?, `cat_id` = ?, `img` = ?, `hide` = ?, `priority` = ? WHERE `id` = ?");
        $stmt->bind_param("ssdisiii", $_POST['title'], $_POST['dsc'], $_POST['price'], $_POST['cat'], $_POST['img'], $_POST['hide'], $_POST['priority'], $_POST['id']);
        if ($stmt->execute()) {
            exitWithSuccess("Product updated successfully");
        } else {
            exitWithError("Error updating product: " . $stmt->error);
        }
    } elseif ($act == "duplicate") {
        // Fetch existing product data
        $stmt = $conn->prepare("SELECT `title`, `dsc`, `price`, `cat_id`, `img`, `hide`, `priority` FROM `prods` WHERE `id` = ?");
        $stmt->bind_param("i", $_POST['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $dup_stmt = $conn->prepare("INSERT INTO `prods` (`title`, `dsc`, `price`, `cat_id`, `img`, `hide`, `priority`) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $dup_stmt->bind_param("ssdisii", $row['title'], $row['dsc'], $row['price'], $row['cat_id'], $row['img'], $row['hide'], $row['priority']);
            if ($dup_stmt->execute()) {
                exitWithSuccess("Product duplicated successfully");
            } else {
                exitWithError("Error duplicating product: " . $dup_stmt->error);
            }
        } else {
            exitWithError("Error fetching product data");
        }
    }
}



$stocks = array();
//get all stocks
$sql = "SELECT `prod_id`, COUNT(id) as num FROM `stock` GROUP BY `prod_id` ORDER BY `prod_id` ASC;";
$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)){
    $prod_id = $row['prod_id'];
    $stocks[$prod_id] = $row['num'];
}

?>



<!DOCTYPE html>

<html lang="en-US">
<link type="text/css" rel="stylesheet" id="dark-mode-custom-link">
<link type="text/css" rel="stylesheet" id="dark-mode-general-link">
<style lang="en" type="text/css" id="dark-mode-custom-style"></style>
<style lang="en" type="text/css" id="dark-mode-native-style"></style>
<style lang="en" type="text/css" id="dark-mode-native-sheet"></style>

<head>
    <?php include(__DIR__ . '/../comp/header.php'); ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <title><?php echo $shop_title; ?> - Products Manager</title>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                                    <h5>Products Manager</h5>
                                    <p>Manage your products</p>
                                </div>
                            </div>
                        </div>
                        <?php  
                        if(isset($_GET['error'])){

                            $error = htmlspecialchars(urldecode($_GET['error']));
                            echo '<div class="alert alert-danger" role="alert">';
                            echo $error;
                            echo '</div>';

                        }elseif(isset($_GET['success'])){
                            $success = htmlspecialchars(urldecode($_GET['success']));
                            echo '<div class="alert alert-success" role="alert">';
                            echo $success;
                            echo '</div>';
                        }
                    ?>



                        <div class="row mt-2 mb-5">



                            <form method="post" class="col-12" id="newProd">
                                <input type="hidden" name="act" value="add">
                                <div class="mb-3">
                                    <label for="name" class="form-label text-white">Title</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark" name="title" required placeholder="Product name">
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label text-white">Description</label>
                                    <textarea class="form-control mb-3 text-white bg-dark" name="dsc" required placeholder="Product description"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label text-white">Image</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark" name="img" required placeholder="Product image">
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label text-white">Category</label>
                                    <select class="form-control mb-3 text-white bg-dark" name="cat" required>
                                        <?php
                                        
                                        $sql = "SELECT * FROM `cats`";
                                        $result = mysqli_query($conn, $sql);
                                        $cats = array();
                                        while($row = mysqli_fetch_assoc($result)){
                                            echo '<option value="'.$row['id'].'">'.$row['title'].'</option>';
                                            //add to array
                                            array_push($cats, $row);

                                        }

                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label text-white">Price</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark" name="price" required placeholder="Product price">
                                </div>
<div class="mb-3">
                                    <label for="name" class="form-label text-white">Priority</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark" name="priority" required placeholder="Product priority">
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label text-white">hide</label>
                                    <select class="form-control mb-3 text-white bg-dark" name="hide" required>
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-danger w-100">Submit</button>
                            </form>

                        </div>




                        <div class="row">
                            <div class="col-lg-12">
                                <div class="dashboard_main_head">
                                    <h5>Products</h5>
                                    <p>Manage your products</p>
                                </div>
                            </div>
                        </div>
<div class="col-lg-4 mb-3 mx-auto">
    <label for="search">Search:</label>
    <input type="text" id="search" class="form-control" placeholder="Enter ID, Username, Method, or Products">
</div>


                        <div class="row">
                            <div class="dash_recent_order py-0 col-lg-12">
                                <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 border-0">ID</th>
                                            <th class="px-4 py-2 border-0">Title</th>
                                            <th class="px-4 py-2 border-0">Price</th>
                                            <th class="px-4 py-2 border-0">Priority</th>
                                            <th class="px-4 py-2 border-0">Stock</th>
                                            <th class="px-4 py-2 border-0">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>                
                                        <?php
                                        $showlogs = mysqli_query($conn, "SELECT * FROM prods ORDER BY id DESC;");
                                        while($row = mysqli_fetch_array($showlogs)) { 

                                            //check if product is an array key
                                            $stock = (array_key_exists($row['id'], $stocks)) ? $stocks[$row['id']] : 0;



                                            echo '
                                                <tr class="names" data-name="'.$row['title'].'">
                                                    <td class="px-4 py-2 border-0">'.$row['id'].'</td>
                                                    <td class="px-4 py-2 border-0">'.$row['title'].'</td>
                                                    <td class="px-4 py-2 border-0">'.$row['price'].'</td>
                                                    <td class="px-4 py-2 border-0">'.$row['priority'].'</td>
                                                    <td class="px-4 py-2 border-0">'.$stock.'</td>

                                                    <td class="px-4 py-2 border-0">
                                                        <a class="download-href" href="add_stock.php?id='.$row['id'].'" style="margin-right:10px;"><span class="badge bg-primary">Stock</span></a>

                                                        <button class="download-href" data-bs-toggle="modal" data-bs-target="#modal'.$row['id'].'" style="margin-right:10px;"><i class="fas fa-pen"></i></button>

                                                        <form method="post" style="display: inline;">
                                                            <input type="hidden" name="id" value="'.$row['id'].'">
                                                            <input type="hidden" name="act" value="del">
                                                            <button onclick="confirmDelete(\''. addslashes($row['title']) .'\', \'' . $row['id'] . '\')" type="button" class="download-href"><i class="fas fa-trash-alt"></i></button>                                                            </form>

                                                        <form method="post" style="display: inline;">
                                                        <input type="hidden" name="id" value="'.$row['id'].'">
                                                        <input type="hidden" name="act" value="duplicate">
                                                        <button type="submit" class="download-href"><i class="fas fa-copy"></i></button>
                                                    </form>
                                                    </td>
                                                </tr>';


                                            echo '<div class="modal fade" id="modal'.$row['id'].'" tabindex="-1" aria-labelledby="modal'.$row['id'].'" style="display: none;" aria-hidden="true">                
                                            <div class="modal-dialog" role="document">
                                                <form method="post">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title text-white">Edit product</h5>
                                                        </div>
                                                        <input type="hidden" name="id" value="'.$row['id'].'">
                                                        <input type="hidden" name="act" value="edit">
                                                        
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="col-form-label text-white" for="recipient-name">Title:</label>
                                                                <input class="form-control mb-3 text-white bg-dark m-0 w-100" name="title" type="text" value="'.$row['title'].'">
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="col-form-label text-white" for="recipient-name">Description:</label>
                                                                <textarea class="form-control mb-3 text-white bg-dark m-0 w-100" name="dsc" rows="5">'.$row['dsc'].'</textarea>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="col-form-label text-white" for="recipient-name">Price:</label>
                                                                <input class="form-control mb-3 text-white bg-dark m-0 w-100" name="price" type="text" value="'.$row['price'].'">
                                                            </div>
                                                            
                                                            
                                                            <div class="mb-3">
    <label class="col-form-label text-white" for="recipient-name">Priority:</label>
    <input class="form-control mb-3 text-white bg-dark m-0 w-100" name="priority" type="text" value="'.$row['priority'].'">
</div>



                                                            <div class="mb-3">
                                                                <label class="col-form-label text-white" for="recipient-name">Image:</label>
                                                                <input class="form-control mb-3 text-white bg-dark m-0 w-100" name="img" type="text" value="'.$row['img'].'">
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="col-form-label text-white" for="recipient-name">hide:</label>
                                                                <select class="form-control mb-3 text-white bg-dark m-0 w-100" name="hide">
                                                                    <option value="0" '.($row['hide'] == 0 ? 'selected' : '').'>No</option>
                                                                    <option value="1" '.($row['hide'] == 1 ? 'selected' : '').'>Yes</option>
                                                                </select>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="col-form-label text-white" for="recipient-name">Category:</label>
                                                                <select class="form-control mb-3 text-white bg-dark m-0 w-100" name="cat">';
                                                                    foreach($cats as $cat) {
                                                                        echo '<option value="'.$cat['id'].'" '.($cat['id'] == $row['cat_id'] ? 'selected' : '').'>'.$cat['title'].'</option>';
                                                                    }

                                                                echo '
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
        </div>
    </main>




    <?php include(__DIR__ . '/../comp/footer.php'); ?>

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/script.js"></script>

    <script>
    $(document).ready(function() {
        $('.select2').select2();

        //for each modal
        $('.select2Modal').select2();
    });
    </script>



    <script>
    const search = document.getElementById("search");

    search.oninput = function() {
        var names = document.getElementsByClassName("names");

        for (var i = 0; i < names.length; i++) {
            names[i].style.display = "none";
        }

        let searchvalue = search.value.toLowerCase();

        for (var i = 0; i < names.length; i++) {
            let title = names[i].querySelector('td:nth-child(2)').innerText.toLowerCase();
            let id = names[i].querySelector('td:nth-child(1)').innerText.toLowerCase();
            let price = names[i].querySelector('td:nth-child(3)').innerText.toLowerCase();
            let stock = names[i].querySelector('td:nth-child(4)').innerText.toLowerCase();

            if (title.includes(searchvalue) || id.includes(searchvalue) || price.includes(searchvalue) || stock.includes(searchvalue)) {
                names[i].style.display = "table-row";
            }
        }
    };




    function confirmDelete(title, id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you really want to delete " + title + "?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        background: '#000', 
        color: '#fff', 
    }).then((result) => {
        if (result.isConfirmed) {
            var form = document.createElement('form');
            document.body.appendChild(form);
            form.method = 'post';
            form.action = '';

            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'id';
            input.value = id;
            form.appendChild(input);

            var action = document.createElement('input');
            action.type = 'hidden';
            action.name = 'act';
            action.value = 'del';
            form.appendChild(action);

            form.submit();
        }
    });
}

</script>



</body>

</html>



