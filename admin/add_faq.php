<?php

session_start();

require_once(__DIR__ . '/../server/db.php');
require_once(__DIR__ . '/../comp/functions.php');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit();
}

$id = $_SESSION["id"];
$username = $_SESSION["username"];
$role = intval(getUserData($id, "role"));
$bal = getUserData($id, "bal");

if ($role != 1) {
    header("location: ../user/dashboard.php");
    exit();
}

// Add or edit FAQ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['act'])) {
        if ($_POST['act'] == 'add') {
            // Add new FAQ
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $content = $_POST['content'];

            $sql = "INSERT INTO faqs (title, content) VALUES ('$title', '$content')";
            if (mysqli_query($conn, $sql)) {
                // FAQ added successfully
                $success_message = 'FAQ added successfully.';
            } else {
                // Handle the error
                $error_message = 'Something went wrong, please try again later.';
            }
        } elseif ($_POST['act'] == 'edit' && isset($_POST['edit_id'])) {
            // Edit FAQ
            $edit_id = $_POST['edit_id'];
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $content = $_POST['content'];

            $sql = "UPDATE faqs SET title='$title', content='$content' WHERE id=$edit_id";
            if (mysqli_query($conn, $sql)) {
                // FAQ updated successfully
                $success_message = 'FAQ updated successfully.';
            } else {
                // Handle the error
                $error_message = 'Something went wrong, please try again later.';
            }
        }
    }
}

// Delete FAQ
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sqlDelete = "DELETE FROM faqs WHERE id=$delete_id";
    if (mysqli_query($conn, $sqlDelete)) {
        // FAQ deleted successfully
        $success_message = 'FAQ deleted successfully.';
    } else {
        // Handle the error
        $error_message = 'Something went wrong, please try again later.';
    }
}

// Fetch all FAQs
$sqlFetchFaqs = "SELECT * FROM faqs";
$resultFetchFaqs = mysqli_query($conn, $sqlFetchFaqs);

?>

<!DOCTYPE html>

<html lang="en-US">
<head>
    <?php include(__DIR__ . '/../comp/header.php'); ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <title><?php echo $shop_title; ?> - FAQ Manager</title>
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
                                    <h5>FAQs</h5>
                                    <p>Manage your FAQs</p>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2 mb-5">
    <?php if (isset($success_message)) : ?>
        <div class="alert alert-success" role="alert">
            <?php echo $success_message; ?>
        </div>
    <?php elseif (isset($error_message)) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

  
    
</div>



<div class="row mt-2 mb-5">




<form method="POST" class="col-lg-12">
    <input type="hidden" name="act" value="<?php echo (isset($_GET['edit_id'])) ? 'edit' : 'add'; ?>">
    <?php if (isset($_GET['edit_id'])) {
        $edit_id = $_GET['edit_id'];
        $sqlEdit = "SELECT * FROM faqs WHERE id=$edit_id";
        $resultEdit = mysqli_query($conn, $sqlEdit);
        $editData = mysqli_fetch_assoc($resultEdit);
    ?>
        <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
        <div class="row">
            <div class="col-md-12">
                <h5 class="text-white mb-3">Title</h5>
                <input type="text" class="form-control form-control-sm form-control-dark mb-3 text-white bg-dark" name="title" required placeholder="FAQ Title" value="<?php echo $editData['title']; ?>">
            </div>
            <div class="col-md-12">
                <h5 class="text-white mb-3">Content</h5>
                <input type="text" class="form-control form-control-sm form-control-dark mb-3 text-white bg-dark" name="content" required placeholder="FAQ Content" value="<?php echo $editData['content']; ?>">
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-danger w-100">Update FAQ</button>
            </div>
        </div>
    <?php } else { ?>
        <div class="row">
            <div class="col-md-12">
                <h5 class="text-white mb-3">Title</h5>
                <input type="text" class="form-control form-control-sm form-control-dark mb-3 text-white bg-dark" name="title" required placeholder="FAQ Title">
            </div>
            <div class="col-md-12">
                <h5 class="text-white mb-3">Content</h5>
                <input type="text" class="form-control form-control-sm form-control-dark mb-3 text-white bg-dark" name="content" required placeholder="FAQ Content">
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-danger w-100">Add FAQ</button>
            </div>
        </div>
    <?php } ?>
</form>

</div>

                        

                        <div class="row">
                            <div class="dash_recent_order py-0 col-lg-12">
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 border-0">ID</th>
                                            <th class="px-4 py-2 border-0">Title</th>
                                            <th class="px-4 py-2 border-0">Content</th>
                                            <th class="px-4 py-2 border-0">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        while ($row = mysqli_fetch_assoc($resultFetchFaqs)) {
                                            echo '<tr>';
                                            echo '<td class="px-4 py-2 border-0">' . $row['id'] . '</td>';
                                            echo '<td class="px-4 py-2 border-0">' . $row['title'] . '</td>';
                                            echo '<td class="px-4 py-2 border-0">' . $row['content'] . '</td>';
                                            echo '<td class="px-4 py-2 border-0">
                                                    <a href="?edit_id=' . $row['id'] . '" class="download-href"><i class="fas fa-edit"></i></a>
                                                    <a href="?delete_id=' . $row['id'] . '" class="download-href"><i class="fas fa-trash-alt"></i></a>
                                                  </td>';
                                            echo '</tr>';
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
