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


    $acts = array("add", "msg", "close");
    if (!in_array($act, $acts)) {
        exitWithError("Invalid action");
    }

    if ($act == "add") {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $msg = mysqli_real_escape_string($conn, $_POST['msg']);

        $sql = "INSERT INTO `tickets` (`title`, `status`, `user_id`) VALUES ('$title', '1', '$id')";
        $result = mysqli_query($conn, $sql);

        //get last id
        $ticket_id = mysqli_insert_id($conn);

        $sql = "INSERT INTO `ticket_msgs` (`ticket_id`, `user_id`, `msg`) VALUES ('$ticket_id', '$id', '$msg')";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            exitWithSuccess("Ticket added successfully");
        } else {
            exitWithError("Error adding ticket");

        }
    }
    if ($act == "close") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);

        $sql = "UPDATE `tickets` SET `status` = '0' WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            exitWithSuccess("Ticket closed successfully");
        } else {
            exitWithError("Error closing ticket");

        }
    }
    if ($act == "msg") {
        $ticket_id = mysqli_real_escape_string($conn, $_POST['id']);
        $msg = mysqli_real_escape_string($conn, $_POST['msg']);


        //get ticket status
        $sql = "SELECT `status` FROM `tickets` WHERE `id` = '$ticket_id'";
        $result = mysqli_query($conn, $sql);
        $ticket = mysqli_fetch_assoc($result);
        $status = $ticket['status'];

        if ($status == 0) {
            exitWithError("Ticket is closed");
        }


        $sql = "INSERT INTO `ticket_msgs` (`ticket_id`, `user_id`, `msg`) VALUES ('$ticket_id', '$id', '$msg')";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            exitWithSuccess("Message sent successfully");
        } else {
            exitWithError("Error sending message");

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
    <?php include(__DIR__ . '/../comp/header.php'); ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <title>
        <?php echo $shop_title; ?> - Support Manager
    </title>
    <style>
        .message-container {
            position: relative;
        }

        .attachment-container {
            text-align: center;
        }

        .message-attachment {
            max-width: 100%;
            height: auto;
            margin: 0 auto;
            /* Center the image horizontally */
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
                                    <h5>Support Manager</h5>
                                    <p>Manage your support tickets</p>
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


                        <div class="row">
                            <div class="dash_recent_order py-0 col-lg-12">
                                <div class="table-responsive">

                                    <table>
                                        <thead>
                                            <tr>
                                                <th class="px-4 py-2 border-0">ID</th>
                                                <th class="px-4 py-2 border-0">User</th>
                                                <th class="px-4 py-2 border-0">Subject</th>
                                                <th class="px-4 py-2 border-0">Status</th>
                                                <th class="px-4 py-2 border-0">Created</th>
                                                <th class="px-4 py-2 border-0">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php
                                            $showlogs = mysqli_query($conn, "SELECT * FROM `tickets` ORDER BY `id` DESC");
                                            while ($row = mysqli_fetch_array($showlogs)) {
                                                $status = $row['status'];

                                                $statusText = ($status == 1) ? "Open" : "Closed";



                                                echo '            
                                            <tr class="names px-4 py-2 border-0" data-name="' . $row['title'] . '">
                                            <td class="px-4 py-2 border-0">' . $row['id'] . '</td>
                                            <td class="px-4 py-2 border-0">' . getUserData($row['user_id'], "username") . '</td>
                                            <td class="px-4 py-2 border-0">' . $row['title'] . '</td>
                                            <td class="px-4 py-2 border-0">' . $statusText . '</td>
                                            <td class="px-4 py-2 border-0">' . date("j M, G:i", strtotime($row['created_at'])) . '</td>
                                            
                                            <td class="px-4 py-2 border-0">
                                            <button class="download-href" data-bs-toggle="modal" data-bs-target="#modal' . $row['id'] . '" style="margin-right:10px;"><i class="fas fa-eye"></i></button>';
                                                if ($status == 1) {
                                                    echo '
                                                <form method="post" style="display: inline;">
                                                    <input type="hidden" name="id" value="' . $row['id'] . '">
                                                    <input type="hidden" name="act" value="close">
                                                    <button type="submit" class="download-href"><i class="fas fa-times"></i></button>
                                                </form>';
                                                }


                                                echo '</td>
                                            </tr>';


                                                echo '<div class="modal fade" id="modal' . $row['id'] . '" tabindex="-1" aria-labelledby="modal' . $row['id'] . '" style="display: none;" aria-hidden="true">                
                                            <div class="modal-dialog" role="document">
                                                <form method="post">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title text-white">Ticket #' . $row['id'] . '</h5>
                                                        </div>
                                                        <input type="hidden" name="id" value="' . $row['id'] . '">
                                                        <input type="hidden" name="act" value="msg">
                                                        
                                                        <div class="modal-body">';

                                                $sql2 = "SELECT * FROM `ticket_msgs` WHERE `ticket_id` = '" . $row['id'] . "' ORDER BY `id` ASC";
                                                $result2 = mysqli_query($conn, $sql2);
                                                while ($row2 = mysqli_fetch_array($result2)) {
                                                    if ($row2['user_id'] == $row['user_id']) {
                                                        echo '<div class="head_notify_box mb-3" role="alert">';
                                                        echo '<b>' . ucfirst(getUserData($row2['user_id'], "username")) . ' - ' . date("j M, G:i", strtotime($row2['created_at'])) . '</b>:<br>';
                                                        echo $row2['msg'];
                                                        $attachmentPath = $row['attachment_path'];
                                                        if (!empty($attachmentPath)) {
                                                            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'mp4'];
                                                            $extension = strtolower(pathinfo($attachmentPath, PATHINFO_EXTENSION));

                                                            // Check if the attachment is an image
                                                            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                                echo '<img src="' . $attachmentPath . '" alt="Attachment" class="message-attachment" style="height: auto !important; width: auto !important; margin-left: 10px; margin-top: 10px;">';
                                                            } elseif ($extension == 'mp4') {
                                                                // Display video if it's an MP4 file
                                                                echo '<video width="100%" height="auto" controls>';
                                                                echo '<source src="' . $attachmentPath . '" type="video/mp4">';
                                                                echo 'Your browser does not support the video tag.';
                                                                echo '</video>';
                                                            }

                                                        }
                                                        echo '</div>';
                                                    } else {
                                                        echo '<div class="head_notify_box2 mb-3" role="alert">';
                                                        echo '<b>' . ucfirst(getUserData($row2['user_id'], "username")) . ' - ' . date("j M, G:i", strtotime($row2['created_at'])) . '</b>:<br>';
                                                        echo $row2['msg'];
                                                        $attachmentPath = $row['attachment_path'];
                                                        if (!empty($attachmentPath)) {
                                                            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'mp4'];
                                                            $extension = strtolower(pathinfo($attachmentPath, PATHINFO_EXTENSION));

                                                            // Check if the attachment is an image
                                                            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                                echo '<img src="' . $attachmentPath . '" alt="Attachment" class="message-attachment" style="height: auto !important; width: auto !important; margin-left: 10px; margin-top: 10px;">';
                                                            } elseif ($extension == 'mp4') {
                                                                // Display video if it's an MP4 file
                                                                echo '<video width="100%" height="auto" controls>';
                                                                echo '<source src="' . $attachmentPath . '" type="video/mp4">';
                                                                echo 'Your browser does not support the video tag.';
                                                                echo '</video>';
                                                            }

                                                        }
                                                        echo '</div>';
                                                    }
                                                }

                                                echo '   </div>';
                                                if ($status == 1) {
                                                    echo '
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="name" class="form-label text-white">Message</label>
                                                                <input type="text" class="form-control mb-3 text-white bg-dark" name="msg" required placeholder="Message">
                                                            </div>';
                                                }
                                                echo '
                                                        <div class="modal-footer">
                                                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal" data-bs-original-title="" title="">Close</button>
                                                            ';
                                                if ($status == 1) {
                                                    echo '
                                                    <button class="btn btn-primary" type="submit" id="btn27">Submit Message</button>';
                                                }
                                                echo '
                                                            
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




    <?php include(__DIR__ . '/../comp/footer.php');  ?>

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/script.js"></script>





</body>

</html>