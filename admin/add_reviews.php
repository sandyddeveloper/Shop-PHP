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

if ($role != 1 && $role != 2) {
    header("location: ../user/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $act = mysqli_real_escape_string($conn, $_POST['act']);

    $acts = array("add", "edit", "del");
    if (!in_array($act, $acts)) {
        exitWithError("Invalid action");
    }

    if ($act == "add") {
        $star_count = mysqli_real_escape_string($conn, $_POST['star_count']);
        $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        $review_text = mysqli_real_escape_string($conn, $_POST['review']);

        $sql = "INSERT INTO `reviews` (`star_count`, `order_id`, `user_id`, `review`) VALUES ('$star_count', '$order_id', '$user_id', '$review_text')";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            exitWithSuccess("Review added successfully");
        } else {
            exitWithError("Error adding review: " . mysqli_error($conn));
        }

    } elseif ($act == "del") {
        $review_id = mysqli_real_escape_string($conn, $_POST['review_id']);

        $sql = "DELETE FROM `reviews` WHERE `review_id` = '$review_id'";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            exitWithSuccess("Review deleted successfully");
        } else {
            exitWithError("Error deleting review");
        }
    } elseif ($act == "edit") {
        $review_id = mysqli_real_escape_string($conn, $_POST['review_id']);
        $star_count = mysqli_real_escape_string($conn, $_POST['star_count']);
        $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        $review_text = mysqli_real_escape_string($conn, $_POST['review']);

        $sql = "UPDATE `reviews` SET `star_count` = '$star_count', `order_id` = '$order_id', `user_id` = '$user_id', `review` = '$review_text' WHERE `review_id` = '$review_id'";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            exitWithSuccess("Review updated successfully");
        } else {
            exitWithError("Error updating review");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en-US">

<head>
    <?php include(__DIR__ . '/../comp/header.php'); ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <title>
        <?php echo $shop_title; ?> - Review Manager
    </title>
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
                                    <h5>New Review</h5>
                                    <p>Manage your reviews</p>
                                </div>
                            </div>
                        </div>
                        <?php
                        if (isset($_GET['error'])) {
                            $error = htmlspecialchars(urldecode($_GET['error']));
                            echo '<div class="alert alert-danger" role="alert">';
                            echo $error;
                            echo '</div>';
                        } elseif (isset($_GET['success'])) {
                            $success = htmlspecialchars(urldecode($_GET['success']));
                            echo '<div class="alert alert-success" role="alert">';
                            echo $success;
                            echo '</div>';
                        }
                        ?>
                        <div class="row mt-2 mb-5">
                            <form method="POST" class="col-lg-12">
                                <input type="hidden" name="act" value="add">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <h5 class="text-white mb-3">Star Count</h5>
                                        <input type="number"
                                            class="form-control form-control-sm mb-3 text-white bg-dark"
                                            name="star_count" required placeholder="Star Count" max="5">
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="text-white mb-3">Order ID</h5>
                                        <input type="text" class="form-control form-control-sm mb-3 text-white bg-dark"
                                            name="order_id" required placeholder="Order ID">
                                    </div>
                                    <div class="col-md-12">
                                        <h5 class="text-white mb-3">User ID</h5>
                                        <input type="text" class="form-control form-control-sm mb-3 text-white bg-dark"
                                            name="user_id" required placeholder="User ID">
                                    </div>
                                    <div class="col-md-12">
                                        <h5 class="text-white mb-3">Review</h5>
                                        <textarea
                                            class="form-control form-control-sm form-control-dark mb-3 text-white bg-dark"
                                            name="review" rows="5" required placeholder="Review"></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-danger w-100">Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="dashboard_main_head text-center">
                                    <div class="table-responsive">
                                        <h5>All Reviews</h5>
                                        <p>Manage your reviews</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="dash_recent_order py-0 col-lg-12">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 border-0">ID</th>
                                            <th class="px-4 py-2 border-0">User ID</th>
                                            <th class="px-4 py-2 border-0">Order ID</th>
                                            <th class="px-4 py-2 border-0">Star Count</th>
                                            <th class="px-4 py-2 border-0">Review</th>
                                            <th class="px-4 py-2 border-0">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $show_reviews = mysqli_query($conn, "SELECT * FROM reviews ORDER BY reviewed_at DESC;");
                                        while ($row = mysqli_fetch_array($show_reviews)) {
                                            echo '
                                        <tr>
                                            <td class="px-4 py-2 border-0">' . $row['review_id'] . '</td>
                                            <td class="px-4 py-2 border-0">' . $row['user_id'] . '</td>
                                            <td class="px-4 py-2 border-0">' . $row['order_id'] . '</td>
                                            <td class="px-4 py-2 border-0">' . $row['star_count'] . '</td>
                                            <td class="px-4 py-2 border-0">' . $row['review'] . '</td>
                                            <td class="px-4 py-2 border-0">
                                                <button class="download-href" data-bs-toggle="modal" data-bs-target="#modal' . $row['review_id'] . '" style="margin-right:10px;"><i class="fas fa-edit"></i></button>
                                                <form method="post" style="display: inline;">
                                                    <input type="hidden" name="review_id" value="' . $row['review_id'] . '">
                                                    <input type="hidden" name="act" value="del">
                                                    <button type="submit" class="download-href"><i class="fas fa-trash-alt"></i></button>
                                                </form>
                                            </td>
                                        </tr>';
                                            echo '<div class="modal fade" id="modal' . $row['review_id'] . '" tabindex="-1" aria-labelledby="modal' . $row['review_id'] . '" style="display: none;" aria-hidden="true">                
                                                <div class="modal-dialog" role="document">
                                                    <form method="post">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title text-white">Edit Review</h5>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="col-form-label text-white" for="recipient-name">Star Count:</label>
                                                                    <input class="form-control mb-3 text-white bg-dark m-0 w-100" name="star_count" type="number" value="' . $row['star_count'] . '" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="col-form-label text-white" for="recipient-name">Order ID:</label>
                                                                    <input class="form-control mb-3 text-white bg-dark m-0 w-100" name="order_id" type="text" value="' . $row['order_id'] . '" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="col-form-label text-white" for="recipient-name">User ID:</label>
                                                                    <input class="form-control mb-3 text-white bg-dark m-0 w-100" name="user_id" type="text" value="' . $row['user_id'] . '" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="col-form-label text-white" for="recipient-name">Review:</label>
                                                                    <textarea class="form-control mb-3 text-white bg-dark m-0 w-100" name="review" rows="5" required>' . $row['review'] . '</textarea>
                                                                </div>
                                                                <input type="hidden" name="review_id" value="' . $row['review_id'] . '">
                                                                <input type="hidden" name="act" value="edit">
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
    </main>

    <?php include(__DIR__ . '/../comp/footer.php'); ?>

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/script.js"></script>

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