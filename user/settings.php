<?php
session_start();


require_once __DIR__ . "/../server/db.php";
require_once __DIR__ . "/../comp/functions.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit();
}

$id = $_SESSION["id"];
$username = $_SESSION["username"];
$role = intval(getUserData($id, "role"));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['change_password'])) {
        $current = mysqli_real_escape_string($conn, $_POST['current']);
        $new = mysqli_real_escape_string($conn, $_POST['new']);
        $confirm = mysqli_real_escape_string($conn, $_POST['confirm']);

        if ($new != $confirm) {
            exitWithError("Passwords do not match");
        }

        if (strlen($new) < 6 || strlen($new) > 20 || strlen($confirm) < 6 || strlen($confirm) > 20) {
            exitWithError("Password must be at least 6 characters");
        }

        $current_hash = password_hash($current, PASSWORD_DEFAULT);
        $new_hash = password_hash($new, PASSWORD_DEFAULT);

        $sql = "SELECT `password` FROM `users` WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $password = $row['password'];
            if (password_verify($current, $password)) {
                $sql = "UPDATE `users` SET `password` = '$new_hash' WHERE `id` = '$id'";
                $result = mysqli_query($conn, $sql);
                if ($result) {
                    exitWithSuccess("Password changed successfully");
                } else {
                    exitWithError("Error changing password");
                }
            } else {
                exitWithError("Current password is incorrect");
            }
        } else {
            exitWithError("Error changing password");
        }
    } elseif (isset($_POST['change_email'])) {
        $current_password = mysqli_real_escape_string($conn, $_POST['current']);
        $new_email = mysqli_real_escape_string($conn, $_POST['new_email']);

        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            exitWithError("Invalid email format");
        }

        $sql = "SELECT `password` FROM `users` WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $password = $row['password'];

            if (password_verify($current_password, $password)) {
                $sql = "UPDATE `users` SET `email` = '$new_email' WHERE `id` = '$id'";
                $result = mysqli_query($conn, $sql);

                if ($result) {
                    exitWithSuccess("Email changed successfully");
                } else {
                    exitWithError("Error changing email");
                }
            } else {
                exitWithError("Current password is incorrect");
            }
        } else {
            exitWithError("Error changing email");
        }
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
    <?php include __DIR__ . "/../comp/header.php"; ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <title><?php echo $shop_title; ?> - Settings</title>
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
                                    <h5>Settings</h5>
                                    <p>Change your account settings</p>
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

                        <div class="container">
                            <div class="row mt-5">
                                <div class="col-md-6 offset-md-3">
                                    <form action="upload_avatar.php" method="post" enctype="multipart/form-data"
                                        class="text-center text-white">
                                        <label for="avatar">Choose a profile picture (max 2MB):</label>
                                        <input type="file" name="avatar" id="avatar" accept="image/*"
                                            class="form-control-file">
                                        <button type="submit" class="btn btn-danger w-100" style="margin-top: 10px !important">Upload avatar</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Form for changing the password -->
                        <form method="post" class="col-lg-12 mt-4">
                            <div class="mb-3">
                                <label class="form-label text-white">Current Password</label>
                                <input type="password" class="form-control mb-3 text-white bg-dark" name="current"
                                    placeholder="Current Password">
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white">New Password</label>
                                <input type="password" class="form-control mb-3 text-white bg-dark" name="new"
                                    placeholder="New Password">
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white">Confirm Password</label>
                                <input type="password" class="form-control mb-3 text-white bg-dark" name="confirm"
                                    placeholder="Confirm Password">
                            </div>
                            <button type="submit" class="btn btn-danger w-100" name="change_password">Change password</button>
                        </form>

                        <!-- Form for changing the email -->
                        <form method="post" class="col-lg-12 mt-4">
                            <div class="mb-3">
                                <label class="form-label text-white">Current Password</label>
                                <input type="password" class="form-control mb-3 text-white bg-dark" name="current"
                                    placeholder="Current Password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white">New Email</label>
                                <input type="email" class="form-control mb-3 text-white bg-dark" name="new_email"
                                    placeholder="New Email" required>
                            </div>
                            <button type="submit" class="btn btn-danger w-100" name="change_email">Change Email</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . "/../comp/footer.php"; ?>

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
