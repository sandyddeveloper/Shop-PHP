<?php

session_start();

require_once('server/db.php');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $maxFileSize = 2 * 1024 * 1024;  // 2MB in bytes
        if ($_FILES['avatar']['size'] <= $maxFileSize) {
            $imageInfo = getimagesize($_FILES['avatar']['tmp_name']);
            if ($imageInfo !== false) {
                $imageType = $imageInfo[2];  // Get the image type

                // Define allowed image types
                $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF];

                if (in_array($imageType, $allowedTypes)) {
                    // Generate a unique filename for the uploaded image
                    $fileName = uniqid() . '_' . $_FILES['avatar']['name'];

                    // Move the uploaded image to the pfp folder
                    $targetDirectory = 'pfp/';
                    $targetPath = $targetDirectory . $fileName;
                    move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath);

                    // Update the user's profile with the avatar path
                    $user_id = $_SESSION['id'];
                    $conn = new mysqli("localhost", "root", "", "shop");

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    // Update the 'pfp' column in the 'users' table
                    $updateQuery = "UPDATE users SET pfp = '$targetPath' WHERE id = $user_id";

                    if ($conn->query($updateQuery) === true) {
                        $conn->close();
                        // Redirect to the user's profile page or wherever you want
                        header("Location: home.php");
                        exit();
                    } else {
                        $error = "Error updating the profile picture in the database: " . $conn->error;
                    }
                } else {
                    $error = "Invalid image format. Please upload a JPEG, PNG, or GIF image.";
                }
            } else {
                $error = "Invalid image file.";
            }
        } else {
            $error = "The uploaded file exceeds the maximum allowed size (2MB).";
        }
    } else {
        $error = "Error uploading the file.";
    }
}

?>
