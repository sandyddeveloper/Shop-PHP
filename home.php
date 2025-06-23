<?php

session_start();

require_once('server/db.php');
require_once 'comp/functions.php';


if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit();
}

$id = $_SESSION["id"];
$username = $_SESSION["username"];
$role = intval(getUserData($id, "role"));
$bal = getUserData($id, "bal");

?>



<!DOCTYPE html>

<html lang="en-US">
<link type="text/css" rel="stylesheet" id="dark-mode-custom-link">
<link type="text/css" rel="stylesheet" id="dark-mode-general-link">
<style lang="en" type="text/css" id="dark-mode-custom-style"></style>
<style lang="en" type="text/css" id="dark-mode-native-style"></style>
<style lang="en" type="text/css" id="dark-mode-native-sheet"></style>


<head>
    <?php include 'comp/header.php'; ?>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <title><?php echo $shop_title; ?> - Home</title>
</head>

<body>

    <main class="main" id="top">

        <?php 
        
        include 'comp/nav.php';

        ?>
        <div class="dashboard_main_sec">

            <div class="container">

                <div class="row">

                    <?php  include 'comp/subnav.php'; ?>

                    <div class="col-9">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="dashboard_main_head">
                                    <h5>Home</h5>
                                    <p>Select a page from the left sidebar to get started.</p>
                                </div>
                            </div>
                        </div>



                        


                        
                    </div>
                </div>
            </div>
        </div>
    </main>




    <?php include 'comp/footer.php'; ?>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/script.js"></script>






</body>

</html>