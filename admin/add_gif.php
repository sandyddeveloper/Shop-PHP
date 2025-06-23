<?php

session_start();

require_once "../server/db.php";
require_once "../comp/functions.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit();
}

$id = $_SESSION["id"];
$username = $_SESSION["username"];
$role = intval(getUserData($id, "role"));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $allowedExtensions = ["gif", "png", "jpg", "jpeg"];
    $maxFileSizeMB = 1000;

    if (isset($_FILES["file"]) && $_FILES["file"]["error"] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES["file"]["tmp_name"];
        $fileName = $_FILES["file"]["name"];
        $fileSize = $_FILES["file"]["size"];
        $fileType = pathinfo($fileName, PATHINFO_EXTENSION);

        if (!in_array(strtolower($fileType), $allowedExtensions)) {
            echo "Invalid file format. Please upload a GIF file.";
            exit();
        }

        if ($fileSize > $maxFileSizeMB * 1024 * 1024) {
            echo "File size exceeds the limit of {$maxFileSizeMB}MB.";
            exit();
        }

        $newFileName = isset($_POST["newFileName"])
            ? $_POST["newFileName"]
            : "";

        if (empty($newFileName)) {
            echo "Please provide a file name.";
            exit();
        }

        $newFileName = preg_replace("/[^a-zA-Z0-9]/", "_", $newFileName);

        $newFilePath = "images/" . $newFileName . "." . $fileType;

        if (move_uploaded_file($fileTmpPath, $newFilePath)) {
            $success_message = "Gif added successfully.";
        } else {
            $success_message = "Error With Uploading";
        }
    } else {
        $success_message = "Try Again";
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
    <?php include "../comp/header.php"; ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <title>
        <?php echo $shop_title; ?> - Settings
    </title>
</head>

<body>

    <main class="main" id="top">

        <?php include "../comp/nav.php"; ?>
        <div class="dashboard_main_sec">

            <div class="container">





                <div class="row">

                    <?php include "../comp/subnav.php"; ?>

                    <div class="col-9">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="dashboard_main_head">
                                    <h5>New GIF</h5>
                                    <p>Manage your Product GIF</p>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2 mb-5">
                            <?php if (isset($success_message)): ?>
                                <div class="alert alert-success" role="alert">
                                    <?php echo $success_message; ?>
                                </div>
                            <?php elseif (isset($error_message)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>



                        </div>



                        <div class="row mt-2 mb-5">
                            <div class="container">
                                <div class="row mt-5">

                                    <div class="col-md-6 offset-md-3">
                                        <form action="add_gif.php" method="post" enctype="multipart/form-data"
                                            class="text-center text-white">

                                            <label for="file">Choose something to upload: everything will be saved into
                                                /images/ folder</label>
                                            <input type="file" name="file" id="file" accept="image/*"
                                                class="form-control-file">
                                            <div class="mb-3">
                                                <label for="newFileName" class="form-label text-white">New File
                                                    Name</label>
                                                <input type="text" class="form-control mb-3 text-white bg-dark"
                                                    name="newFileName" id="newFileName" placeholder="New Name">
                                            </div>
                                            <button type="submit" class="btn btn-primary mt-3">Upload gif</button>
                                        </form>
                                    </div>
                                </div>
                            </div>







                        </div>






                    </div>
                </div>
            </div>
        </div>
    </main>




    <?php include "../comp/footer.php"; ?>

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