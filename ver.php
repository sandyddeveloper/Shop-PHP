<?php

require_once('server/db.php');

if(isset($_GET['email']) && !empty($_GET['email']) AND isset($_GET['hash']) && !empty($_GET['hash'])){

    // Verify data
    $email = mysqli_real_escape_string($conn, $_GET['email']); // Set email variable
    $hash = mysqli_real_escape_string($conn, $_GET['hash']); // Set hash variable

    $result = mysqli_query($conn,"SELECT * FROM users WHERE `email`='$email';");
    $row = mysqli_fetch_array($result);
    if(mysqli_num_rows($result) > 0){

        if($row['hash'] == $hash){

            $sql = "UPDATE `users` SET `verified`='1' WHERE `email`='$email'";
            if ($stmt = $conn->prepare($sql)) {
                if ($stmt->execute()) {
                    $success = "Your account has been verified. You can now login.";
                    $success = urlencode($success);
                    header("location: /auth/login.php?success=" . $success);
                    exit(); // Ensure that you exit after the header
                } else {
                    $error = "Verification failed contact support.";
                    $error = urlencode($error);
                    header("location: /auth/login.php?error=" . $error);
                    exit(); // Ensure that you exit after the header
                }
                $stmt->close();
            }
        } else {
            $error = "Verification failed. Please check your email and try again.";
            $error = urlencode($error);
            header("location: /auth/login.php?error=" . $error);
            exit(); // Ensure that you exit after the header
        }
    } else {
        $error = "User not found. Please check your email and try again.";
        $error = urlencode($error);
        header("location: /auth/login.php?error=" . $error);
        exit(); // Ensure that you exit after the header
    }
}
?>
