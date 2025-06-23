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
$bal = getUserData($id, "bal");

if($role != 1 &&  $role !=3){
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
    $pri = mysqli_real_escape_string($conn, $_POST['pri']);
    
    $stmt = $conn->prepare("INSERT INTO `cats` (`title`, `pri`) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $pri);
    if ($stmt->execute()) {
        exitWithSuccess("Category added successfully");
    } else {
        exitWithError("Error adding category: " . $stmt->error);
    }
}

if ($act == "del") {
    $id = mysqli_real_escape_string($conn, $_POST['id']);

    $stmt = $conn->prepare("DELETE FROM `cats` WHERE `id` = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        exitWithSuccess("Category deleted successfully");
    } else {
        exitWithError("Error deleting category");
    }
}

if ($act == "edit") {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $pri = mysqli_real_escape_string($conn, $_POST['pri']);

    $stmt = $conn->prepare("UPDATE `cats` SET `title` = ?, `pri` = ? WHERE `id` = ?");
    $stmt->bind_param("ssi", $title, $pri, $id);
    if ($stmt->execute()) {
        exitWithSuccess("Category updated successfully");
    } else {
        exitWithError("Error updating category");
    }
    
    
    unset($_SESSION['csrf_token']);
 }

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
    <title><?php echo $shop_title; ?> - Category Manager</title>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<style>
.full-width-container {
            width: 100%;
            margin: 0;
            padding: 0 15px; /* Adjust padding as needed */
        }
        .row {
            margin-right: 0;
            margin-left: 0;
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
                                    <h5>New Category</h5>
                                    <p>Manage your categories</p>
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
                            <form method="POST" class="col-lg-12">
                                <input type="hidden" name="act" value="add">
                                <div class="mb-3">
                                    <h5 class="text-white mb-2">Category name</h5>
                                    <input type="text" class="form-control form-control-sm form-control-dark mb-3 text-white bg-dark" name="title" required placeholder="Category name">
                                </div>
                                <div class="mb-3">
                                    <h5 class="text-white  mb-2">Priority (1-9999)</h5>
                                    <input type="text" class="form-control form-control-sm form-control-dark mb-3 text-white bg-dark" name="pri" required placeholder="Priority">
                                </div>
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-danger w-100">Submit</button>
                                </div>
                            </form>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="dashboard_main_head text-center">
                                    <h5>All Categories</h5>
                                    <p>Manage your categories</p>
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
                                                <th class="px-4 py-2 border-0">Name</th>
                                                <th class="px-4 py-2 border-0">Priority</th>
                                                <th class="px-4 py-2 border-0">Date</th>
                                                <th class="px-4 py-2 border-0">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
$showlogs = mysqli_query($conn, "SELECT * FROM cats ORDER BY id DESC;");
while ($row = mysqli_fetch_array($showlogs)) {
    echo '<tr class="names" data-name="' . $row['title'] . '">
            <td class="px-4 py-2 border-0">' . $row['id'] . '</td>
            <td class="his-img px-4 py-2 border-0">' . $row['title'] . '</td>
            <td class="px-4 py-2 border-0">' . $row['pri'] . '</td>
            <td class="px-4 py-2 border-0">' . date("j M, G:i", strtotime($row['created_at'])) . '</td>
            <td class="px-4 py-2 border-0">
                <button class="download-href" data-bs-toggle="modal" data-bs-target="#modal' . $row['id'] . '"><i class="fas fa-edit"></i></button>
                <form method="post" style="display: inline;" id="deleteForm_' . $row['id'] . '">
                    <input type="hidden" name="id" value="' . $row['id'] . '">
                    <input type="hidden" name="act" value="del">
                    <button type="button" class="download-href" onclick="confirmDelete(' . $row['id'] . ')"><i class="fas fa-trash-alt"></i></button>
                </form>
            </td>
          </tr>';

    echo '<div class="modal fade" id="modal' . $row['id'] . '" tabindex="-1" aria-labelledby="modal' . $row['id'] . '" style="display: none;" aria-hidden="true">                
            <div class="modal-dialog" role="document">
                <form method="post">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-white">Edit category</h5>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="col-form-label text-white" for="recipient-name">Title:</label>
                                <input class="form-control mb-3 text-white bg-dark m-0 w-100" name="title" type="text" value="' . $row['title'] . '">
                                <input type="hidden" name="id" value="' . $row['id'] . '">
                                <input type="hidden" name="act" value="edit">
                            </div>
                            <div class="mb-3">
                                <label class="col-form-label text-white" for="recipient-name">Priority:</label>
                                <input class="form-control mb-3 text-white bg-dark m-0 w-100" name="pri" type="text" value="' . $row['pri'] . '">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
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
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // If the user confirms, submit the corresponding form for deletion
            document.getElementById('deleteForm_' + categoryId).submit();
        }
    });
}

</script>
</body>
</html>
