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


if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $act = mysqli_real_escape_string($conn, $_POST['act']);



    $acts = array("add", "edit", "del");
    if(!in_array($act, $acts)){
        exitWithError("Invalid action");
    }

    if ($act == "add") {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $img = mysqli_real_escape_string($conn, $_POST['img']);
        $cat = mysqli_real_escape_string($conn, $_POST['cat']);
        $dsc = $_POST['dsc'];
        $pri = mysqli_real_escape_string($conn, $_POST['pri']);
        $prods = $_POST['prods'];

        // Debugging output
        // echo "<pre>";
        // echo "Debugging - Add Action\n";
        // echo "Title: $title\n";
        // echo "Image: $img\n";
        // echo "Category: $cat\n";
        // echo "Description: $dsc\n";
        // echo "Priority: $pri\n";
        // echo "Products: ";
        // print_r($prods);
        // echo "</pre>";

        // Assuming prods is an array of product IDs
        if (!empty($prods)) {
            $sql2 = "UPDATE `prods` SET `group_id` = '$id' WHERE `id` IN (" . implode(',', array_map('intval', $prods)) . ")";
            // Debugging output
           // echo "<pre>SQL Query for Updating Products: $sql2</pre>";

            $result = mysqli_query($conn, $sql2);
          
        }

        $prods_imploded = implode(",", array_map('intval', $prods));
        $sql = "INSERT INTO `groups` (`title`, `img`, `cat_id`, `prods`, `dsc`, `pri`) VALUES ('$title', '$img', '$cat', '$prods_imploded', '$dsc', '$pri')";
        
   
       

        $result = mysqli_query($conn, $sql);

        if ($result) {
            exitWithSuccess("Group added successfully");
        } else {
            exitWithError("Error adding group: " . mysqli_error($conn));
        }
    }

    if($act == "del"){
        $id = mysqli_real_escape_string($conn, $_POST['id']);

        $sql = "DELETE FROM `groups` WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        if($result){
            exitWithSuccess("Group deleted successfully");
        }else{
            exitWithError("Error deleting Group");

        }
    }
    if($act == "edit"){
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $img = mysqli_real_escape_string($conn, $_POST['img']);
        $cat = mysqli_real_escape_string($conn, $_POST['cat']);
        $dsc = $_POST['dsc'];
        $pri = mysqli_real_escape_string($conn, $_POST['pri']);
        $prods = $_POST['prods'];

        $oldprods = $_POST['oldprods'];




        if($oldprods != null){
            $sql = "UPDATE `prods` SET `group_id` = '0' WHERE";
            foreach($oldprods as $prod){
                //update group_id in products table
                $sql .= " `id` = '$prod' OR";
            }

            $sql = rtrim($sql, "OR");
            $sql .= ";";

            $result = mysqli_query($conn, $sql);
        }
        


        if($prods != null){
            $sql = "UPDATE `prods` SET `group_id` = '$id' WHERE";
            foreach($prods as $prod){
                //update group_id in products table
                $sql .= " `id` = '$prod' OR";
            }

            $sql = rtrim($sql, "OR");
            $sql .= ";";

            $result = mysqli_query($conn, $sql);
        }

        $prods = implode(",", $prods);


        $sql = "UPDATE `groups` SET `title` = '$title', `img` = '$img', `cat_id` = '$cat', `prods` = '$prods', `dsc` = '$dsc' , `pri` = '$pri' WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        if($result){
            exitWithSuccess("Group updated successfully");
        }else{
            exitWithError("Error updating Group");

        }
    }
}



?>


<!DOCTYPE html>
<html lang="en-US">

<head>
    <?php include(__DIR__ . '/../comp/header.php'); ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/select2.min.css" />
    <title><?php echo $shop_title; ?> - Groups Manager</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    
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
                                    <h5>Groups Manager</h5>
                                    <p>Manage your groups here</p>
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
                            <form method="post" class="col-12" id="newProd">
                                <input type="hidden" name="act" value="add">
                                <div class="mb-3">
                                    <label for="name" class="form-label text-white">Title</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark" name="title"
                                        required placeholder="Group name">
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label text-white">Image</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark" name="img" required
                                        placeholder="Group image">
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label text-white">Description</label>
                                    <textarea class="form-control mb-3 text-white bg-dark" name="dsc" required
                                        placeholder="Group description"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label text-white">Priority (1-9999)</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark" name="pri" required
                                        placeholder="Priority">
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
                                            array_push($cats, $row);
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="mb-3 d-flex flex-column">
                                    <label for="name" class="form-label text-white">Products</label>
                                    <select class="form-control mb-3 text-white bg-dark select2" name="prods[]" multiple
                                        required>
                                        <?php
                                        $sql = "SELECT * FROM `prods`";
                                        $result = mysqli_query($conn, $sql);
                                        $prods = array();
                                        while($row = mysqli_fetch_assoc($result)){
                                            echo '<option value="'.$row['id'].'">'.$row['title'].'</option>';
                                            array_push($prods, $row);
                                        }
                                        ?>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-danger w-100">Submit</button>
                            </form>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="dashboard_main_head text-center">
                                    <h5>All Groups</h5>
                                    <p>Manage your groups here</p>
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
                                            <th class="px-4 py-2 border-0">Title</th>
                                            <th class="px-4 py-2 border-0">Date</th>
                                            <th class="px-4 py-2 border-0">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $showlogs = mysqli_query($conn, "SELECT * FROM `groups` ORDER BY id DESC;");
                                        if (!$showlogs) {
                                            die('Query failed: ' . mysqli_error($conn));
                                        }
                                        while($row = mysqli_fetch_array($showlogs)) { 
                                            echo '<tr class="names" data-name="'.$row['title'].'">
                                                <td class="px-4 py-2 border-0">'.$row['id'].'</td>
                                                <td class="px-4 py-2 border-0">'.$row['title'].'</td>
                                                <td class="px-4 py-2 border-0">' . date("j M, G:i", strtotime($row['created_at'])) . '</td>
                                                <td class="px-4 py-2 border-0">
                                                    <button class="download-href" data-bs-toggle="modal" data-bs-target="#modal'.$row['id'].'" style="margin-right:10px;"><i class="fas fa-pen"></i></button>
                                                     <form method="post" style="display: inline;" id="deleteForm_' . $row['id'] . '">
                    <input type="hidden" name="id" value="' . $row['id'] . '">
                    <input type="hidden" name="act" value="del">
                    <button type="button" class="download-href" onclick="confirmDelete(' . $row['id'] . ')"><i class="fas fa-trash-alt"></i></button>
                </form>
                                                </td>
                                            </tr>';
                                            echo '<div class="modal fade" id="modal'.$row['id'].'" aria-labelledby="modal'.$row['id'].'" data-focus="false" aria-hidden="true">                
                                                <div class="modal-dialog modal-lg" role="document">
                                                    <form method="post">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title text-white">Edit Group</h5>
                                                            </div>
                                                            <input type="hidden" name="id" value="'.$row['id'].'">
                                                            <input type="hidden" name="act" value="edit">
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="col-form-label text-white" for="recipient-name">Title:</label>
                                                                    <input class="form-control mb-3 text-white bg-dark m-0 w-100" name="title" value="'.$row['title'].'" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="col-form-label text-white" for="recipient-name">Image:</label>
                                                                    <input class="form-control mb-3 text-white bg-dark m-0 w-100" name="img" value="'.$row['img'].'" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="name" class="form-label text-white">Description</label>
                                                                    <textarea class="form-control mb-3 text-white bg-dark" name="dsc" required placeholder="Group description">'.$row['dsc'].'</textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="name" class="form-label text-white">Priority (1-9999)</label>
                                                                    <input type="text" class="form-control mb-3 text-white bg-dark" name="pri" required value="'.$row['pri'].'">
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
                                                                <div class="mb-3 d-flex flex-column">
                                                                    <label class="col-form-label text-white" for="recipient-name">Products:</label>
                                                                    <select class="form-control mb-3 text-white bg-dark m-0 w-100 select2Modal" data-dropdown-parent="#modal'.$row['id'].'" name="prods[]" multiple>';
                                                                        $prodsSelected = $row['prods'];
                                                                        $prodsSelected = explode(",", $prodsSelected);
                                                                        foreach($prods as $prod) {
                                                                            echo '<option value="'.$prod['id'].'" '.(in_array($prod['id'], $prodsSelected) ? 'selected' : '').'>'.$prod['title'].'</option>';
                                                                        }
                                                                    echo '
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3 d-flex flex-column">
                                                                    <select hidden class="form-control mb-3 text-white bg-dark m-0 w-100" name="oldprods[]" multiple>';
                                                                        foreach($prods as $prod) {
                                                                            echo '<option value="'.$prod['id'].'" '.(in_array($prod['id'], $prodsSelected) ? 'selected' : '').'>'.$prod['title'].'</option>';
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
    <style>
    .swal-black-content {
        background-color: grey;
        color: white;
    }
    
    

    .swal-black {
    background-color: #333;
    color: #fff;
}

.swal-black-header,
.swal-black-title {
    color: #fff;
}

.swal-black-cancel-button {
    background-color: #666;
    color: #fff;
}

.swal-black-confirm-button {
    background-color: #d33;
    color: #fff;
}


</style>

<script>
    function confirmDelete(categoryId) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            customClass: {
                popup: 'swal-black', // Add a class for styling
                header: 'swal-black-header',
                title: 'swal-black-title',
                cancelButton: 'swal-black-cancel-button',
                confirmButton: 'swal-black-confirm-button',
                content: 'swal-black-content', // Add a class for content styling
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // If the user confirms, submit the corresponding form for deletion
                document.getElementById('deleteForm_' + categoryId).submit();
            }
        });
    }
</script>


    <script>
        $(document).ready(function () {
            $('.select2').select2();
            $('.select2Modal').select2();
        });
    </script>

    <script>
        const search = document.getElementById("search");

        search.oninput = function () {
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
