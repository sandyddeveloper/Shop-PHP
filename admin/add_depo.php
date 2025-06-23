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

if($role != 1 && $role !=2){
    header("location: ../user/dashboard.php");
    exit();
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
    <title><?php echo $shop_title; ?> - Deposits Manager</title>
    
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
                                    <h5>All Deposits</h5>
                                    <p>Here you can see all deposits made by users.</p>
                                </div>
                            </div>
                        </div>
                        <?php  
                        if(isset($_GET['error'])){

                            $error = htmlspecialchars(urldecode($_GET['error']));
                            echo '<div class="alert alert-danger" role="alert">';
                            echo $error;
                            echo '</div>';

                        }elseif(isset($_GET['success'])){
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
                                    <tbody>
                                        <tr>
                                            <th class="px-4 py-2 border-0">ID</th>
                                            <th class="px-4 py-2 border-0">Username</th>
                                            <th class="px-4 py-2 border-0">Amount</th>
                                            <th class="px-4 py-2 border-0">Method</th>
                                            <th class="px-4 py-2 border-0">Status</th>
                                            <th class="px-4 py-2 border-0">Date</th>
                                            <th class="px-4 py-2 border-0">Actions</th>

                                        </tr>
                                    
                                </tbody>


                                <tbody>
                                <?php
                                    $showlogs = mysqli_query($conn, "SELECT * FROM `orders` WHERE `type` = 'deposit' ORDER BY `id` DESC");
                                    while($row = mysqli_fetch_array($showlogs)) { 

                                        if($row['payment_method'] == "Cashapp"){
                                            $uqid = $id.'-'.$row['cashapp_note'];
                                        }else{
                                            $uqid = $row['uqid'];
                                        }

                                        $link = ($row['payment_method'] == "Cashapp") ? 'order.php?id='.$row['id'] : $row['link'];


                                        if($row['status'] == 0){
                                            $status = '<span class="text-white badge bg-danger">Unknown</span>';
                                        }elseif($row['status'] == 1){
                                            $status = '<span class="text-white badge bg-warning">Pending</span>';
                                        }elseif($row['status'] == 2){
                                            $status = '<span class="text-white badge bg-success">Completed</span>';
                                        }elseif($row['status'] == 3){
                                            $status = '<span class="text-white badge bg-danger">Failed</span>';
                                        }
                                        elseif($row['status'] == 4){
                                            $status = '<span class="text-white badge bg-danger">New</span>';
                                        }


                                        echo '            
                                        <tr class="names" data-name="'.$row['id'].'">
                                            <td class="px-4 py-2 border-0">'.$row['id'].'</td>
                                            <td class="px-4 py-2 border-0">'.getUserData($row['user_id'], "username").'</td>
                                            <td class="px-4 py-2 border-0">'.$row['amount'].'</td>
                                            <td class="px-4 py-2 border-0">'.$row['payment_method'].'</td>
                                            <td class="px-4 py-2 border-0">'.$status.'</td>
                                            <td class="px-4 py-2 border-0">' . date("j M, G:i", strtotime($row['created_at'])) . '
                                                    <td class="px-4 py-2 border-0"><a href="' . $link . '" target="_blank"><i class="fas fa-eye download-href"></i></a></td>
                                        </tr>';



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