<?php

session_start();

require_once('../server/db.php');
require_once '../comp/functions.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit();
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(128));
}

$id = $_SESSION["id"];
$username = $_SESSION["username"];
$role = intval(getUserData($id, "role"));
$bal = getUserData($id, "bal");

if ($role != 1) {
    header("location: ../user/dashboard.php");
    exit();
}

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        exitWithError("CSRF token validation failed.");
    }

    $gif_url = $_POST['gif_url'];
    $expiry_date = $_POST['expiry_date'];
    $redirect_url = $_POST['redirect_url'];

    if ($_POST['act'] == 'add') {
        // Prepared statement for adding new ad
        $stmt = $conn->prepare("INSERT INTO ads (gif_url, expiry_date, redirect_url) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $gif_url, $expiry_date, $redirect_url);
    } elseif ($_POST['act'] == 'edit' && isset($_POST['edit_id'])) {
        // Prepared statement for editing an ad
        $edit_id = $_POST['edit_id'];
        $stmt = $conn->prepare("UPDATE ads SET gif_url = ?, expiry_date = ?, redirect_url = ? WHERE id = ?");
        $stmt->bind_param("sssi", $gif_url, $expiry_date, $redirect_url, $edit_id);
    }

    if (isset($stmt) && $stmt->execute()) {
        $success_message = ($_POST['act'] == 'add') ? 'Advertisement added successfully.' : 'Advertisement updated successfully.';
    } else {
        $error_message = 'Something went wrong, please try again later.';
    }

    if (isset($stmt)) {
        $stmt->close();
    }
}

// Delete ad
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM ads WHERE id = ?");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        $success_message = 'Advertisement deleted successfully.';
    } else {
        $error_message = 'Something went wrong, please try again later.';
    }

    $stmt->close();
}


$resultFetchAds = $conn->query("SELECT * FROM ads");

?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <?php include '../comp/header.php'; ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <title><?php echo $shop_title; ?> - Ads Manager</title>
</head>

<body>

    <main class="main" id="top">

        <?php
        include '../comp/nav.php';
        ?>
        <div class="dashboard_main_sec">

            <div class="container">
                <div class="row">
                    <?php include '../comp/subnav.php'; ?>
                    <div class="col-9">

                        <div class="row mt-2 mb-5">
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

                        </div>

                        

                        <div class="row mt-2 mb-5">
                            <h5 class="text-white mb-3">New Ad</h5>
                            <form method="POST" class="col-lg-12">
                                <input type="hidden" name="act" value="<?php echo (isset($_GET['edit_id'])) ? 'edit' : 'add'; ?>">
                                <?php if (isset($_GET['edit_id'])) {
                                    $edit_id = $_GET['edit_id'];
                                    $sqlEdit = "SELECT * FROM ads WHERE id=$edit_id";
                                    $resultEdit = mysqli_query($conn, $sqlEdit);
                                    $editData = mysqli_fetch_assoc($resultEdit);
                                ?>
                                    <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h5 class="text-white mb-3">Gif Url</h5>
                                            <input type="text" class="form-control form-control-sm form-control-dark mb-3 text-white bg-dark" name="gif_url" required placeholder="Gif Url" value="<?php echo $editData['gif_url']; ?>">
                                        </div>
                                        <div class="col-md-12">
                                            <h5 class="text-white mb-3">Redirect Url</h5>
                                            <input type="text" class="form-control form-control-sm form-control-dark mb-3 text-white bg-dark" name="redirect_url" required placeholder="Redirect Url" value="<?php echo $editData['redirect_url']; ?>">
                                        </div>
                                        <div class="col-md-12">
                                            <h5 class="text-white mb-3">Date</h5>
                                            <input type="date" class="form-control form-control-sm form-control-dark mb-3 text-white bg-dark" name="expiry_date" required value="<?php echo $editData['expiry_date']; ?>">
                                        </div>
                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-danger w-100">Update Advertisement</button>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h5 class="text-white mb-3">Gif Url</h5>
                                            <input type="text" class="form-control form-control-sm form-control-dark mb-3 text-white bg-dark" name="gif_url" required placeholder="Gif Url">
                                        </div>
                                        <div class="col-md-12">
                                            <h5 class="text-white mb-3">Redirect Url</h5>
                                            <input type="text" class="form-control form-control-sm form-control-dark mb-3 text-white bg-dark" name="redirect_url" required placeholder="Redirect Url">
                                        </div>
                                        <div class="col-md-12">
                                            <h5 class="text-white mb-3">Date</h5>
                                            <input type="date" class="form-control form-control-sm form-control-dark mb-3 text-white bg-dark" name="expiry_date" required>
                                        </div>
                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-danger w-100">Add Advertisement</button>
                                        </div>
                                    </div>
                                <?php } ?>
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            </form>
                        </div>
                        
                        <div class="row">
                            <div class="dash_recent_order py-0 col-lg-12">
                                <table>
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Gif Url</th>
                                        <th>Redirect Url</th>
                                        <th>Expiry Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($row = mysqli_fetch_assoc($resultFetchAds)) {
                                        echo '<tr>';
                                        echo '<td>' . $row['id'] . '</td>';
                                        echo '<td>' . $row['gif_url'] . '</td>';
                                        echo '<td>' . $row['redirect_url'] . '</td>';
                                        echo '<td>' . $row['expiry_date'] . '</td>';
                                        echo '<td>
                                                <a href="?edit_id=' . $row['id'] . '" class="download-href"><i class="fas fa-pen"></i></i></a>
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
    </main>


    <?php include '../comp/footer.php'; ?>

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/script.js"></script>

</body>

</html>