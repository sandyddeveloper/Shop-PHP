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

if($role != 1 && $role !=2 && $role !=3){
    header("location: ../user/dashboard.php");
    exit();
}


if(isset($_GET['type'])){
    $type = mysqli_real_escape_string($conn, $_GET['type']);
}else{
    $type = "24h";

}


$types = array("24h", "7d", "30d", "1y", "all");

if(!in_array($type, $types)){
    $type = "24h";
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
    <title><?php echo $shop_title; ?> - Analytics</title>
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
                                    <h5>Analytics</h5>
                                    <p>View your shop's analytics</p>
                                </div>
                            </div>
                            
                            <div class="col-lg-12">
                                <div class="heading_btn">
                                    <form action="admin.php" method="get"
                                        class="d-flex justify-content-end ml-4">

                                        <select name="type" id="type"
                                            class="form-select form-select-sm bg-transparent text-white">
                                            <option class="bg-dark text-white" value="24h"
                                                <?php if($type == "24h"){echo "selected";} ?>>24 Hours</option>
                                            <option class="bg-dark text-white" value="7d"
                                                <?php if($type == "7d"){echo "selected";} ?>>7 Days</option>
                                            <option class="bg-dark text-white" value="30d"
                                                <?php if($type == "30d"){echo "selected";} ?>>30 Days</option>
                                            <option class="bg-dark text-white" value="1y"
                                                <?php if($type == "1y"){echo "selected";} ?>>1 Year</option>
                                            <option class="bg-dark text-white" value="all"
                                                <?php if($type == "all"){echo "selected";} ?>>All Time</option>
                                        </select>
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            style="height: 44px;margin-left: 10px;">Filter</button>
                                    </form>
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

                        <?php

                            //total orders
                            //total amount 

                            if($type == "24h"){
                                
                                $sql = "SELECT COUNT(*) as total_orders, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) AND `type` = 'buy'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_orders = $row['total_orders'];
                                $total_orders_amount = $row['total_amount'];

                                $sql = "SELECT COUNT(*) as total_orders, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) AND `type` = 'deposit'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_deposits = $row['total_orders'];
                                $total_deposits_amount = $row['total_amount'];

                                $sql = "SELECT COUNT(*) as total_tickets FROM tickets WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_tick = $row['total_tickets'];

                                $sql = "SELECT COUNT(*) as total_review FROM reviews WHERE reviewed_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_reviews = $row['total_review'];

                                $sql = "SELECT COUNT(*) as total_user FROM users WHERE start >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_users = $row['total_user'];

                                
                                $sql = "SELECT COUNT(*) as closed_ticket FROM tickets WHERE created_at  >= DATE_SUB(NOW() AND status = 0 , INTERVAL 1 DAY)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $closed_tickets = $row['closed_ticket'];

                                $sql = "SELECT COUNT(*) as active_ticket FROM tickets WHERE created_at  >= DATE_SUB(NOW() AND status = 1 , INTERVAL 1 DAY)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_tickets_active = $row['active_ticket'];

                                $sql = "SELECT COUNT(*) as cash_app, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) AND `type` = 'buy' AND payment_method like 'Cashapp' ";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $cashapp = $row['cash_app'];
                                
                                
                                $sql = "SELECT COUNT(*) as coinbase, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) AND `type` = 'buy' AND payment_method like 'Coinbase'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $coinbase = $row['coinbase'];
                                
                                $sql = "SELECT COUNT(*) as balances, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) AND `type` = 'buy' AND payment_method like 'Balance'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $balance = $row['balances'];
                                
                                
                                

                            }elseif($type == "7d"){
                                
                                $sql = "SELECT COUNT(*) as total_orders, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND `type` = 'buy'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_orders = $row['total_orders'];
                                $total_orders_amount = $row['total_amount'];

                                $sql = "SELECT COUNT(*) as total_orders, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND `type` = 'deposit'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_deposits = $row['total_orders'];
                                $total_deposits_amount = $row['total_amount'];

                                $sql = "SELECT COUNT(*) as total_tickets FROM tickets WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_tick = $row['total_tickets'];

                                $sql = "SELECT COUNT(*) as total_review FROM reviews WHERE reviewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_reviews = $row['total_review'];

                                $sql = "SELECT COUNT(*) as total_user FROM users WHERE start >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_users = $row['total_user'];

                                $sql = "SELECT COUNT(*) as closed_ticket FROM tickets WHERE created_at  >= DATE_SUB(NOW() AND status = 0 , INTERVAL 7 DAY)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $closed_tickets = $row['closed_ticket'];

                                $sql = "SELECT COUNT(*) as active_ticket FROM tickets WHERE created_at  >= DATE_SUB(NOW() AND status = 1 , INTERVAL 7 DAY)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_tickets_active = $row['active_ticket'];
                                
                                 $sql = "SELECT COUNT(*) as cash_app, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND `type` = 'buy' AND payment_method like 'Cashapp' ";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $cashapp = $row['cash_app'];
                                
                                
                                $sql = "SELECT COUNT(*) as coinbase, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND `type` = 'buy' AND payment_method like 'Coinbase'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $coinbase = $row['coinbase'];
                                
                                $sql = "SELECT COUNT(*) as balances, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND `type` = 'buy' AND payment_method like 'Balance'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $balance = $row['balances'];

                            }elseif($type == "30d"){

                                $sql = "SELECT COUNT(*) as total_orders, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND `type` = 'buy'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_orders = $row['total_orders'];
                                $total_orders_amount = $row['total_amount'];

                                $sql = "SELECT COUNT(*) as total_orders, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND `type` = 'deposit'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_deposits = $row['total_orders'];
                                $total_deposits_amount = $row['total_amount'];

                                $sql = "SELECT COUNT(*) as total_review FROM reviews WHERE reviewed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_reviews = $row['total_review'];

                                $sql = "SELECT COUNT(*) as total_tickets FROM tickets WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_tick = $row['total_tickets'];

                                $sql = "SELECT COUNT(*) as total_user FROM users WHERE start >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_users = $row['total_user'];

                                $sql = "SELECT COUNT(*) as closed_ticket FROM tickets WHERE status = 0  AND created_at  >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $closed_tickets = $row['closed_ticket'];

                                $sql = "SELECT COUNT(*) as active_ticket FROM tickets WHERE created_at  >= DATE_SUB(NOW() AND status = 1 , INTERVAL 30 DAY)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_tickets_active = $row['active_ticket'];
                                
                                
                                 $sql = "SELECT COUNT(*) as cash_app, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND `type` = 'buy' AND payment_method like 'Cashapp' ";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $cashapp = $row['cash_app'];
                                
                                
                                $sql = "SELECT COUNT(*) as coinbase, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND `type` = 'buy' AND payment_method like 'Coinbase'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $coinbase = $row['coinbase'];
                                
                                $sql = "SELECT COUNT(*) as balances, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND `type` = 'buy' AND payment_method like 'Balance'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $balance = $row['balances'];

                            }elseif($type == "1y"){

                                $sql = "SELECT COUNT(*) as total_orders, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR) AND `type` = 'buy'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_orders = $row['total_orders'];
                                $total_orders_amount = $row['total_amount'];

                                $sql = "SELECT COUNT(*) as total_orders, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR) AND `type` = 'deposit'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_deposits = $row['total_orders'];
                                $total_deposits_amount = $row['total_amount'];

                                $sql = "SELECT COUNT(*) as total_tickets FROM tickets WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_tick = $row['total_tickets'];

                                $sql = "SELECT COUNT(*) as total_review FROM reviews WHERE reviewed_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_reviews = $row['total_review'];

                                $sql = "SELECT COUNT(*) as total_user FROM users WHERE start >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_users = $row['total_user'];

                                $sql = "SELECT COUNT(*) as closed_ticket FROM tickets WHERE status = 0  AND created_at  >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $closed_tickets = $row['closed_ticket'];

                                $sql = "SELECT COUNT(*) as active_ticket FROM tickets WHERE created_at  >= DATE_SUB(NOW() AND status = 1 , INTERVAL 1 YEAR)";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_tickets_active = $row['active_ticket'];
                                
                                 $sql = "SELECT COUNT(*) as cash_app, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR) AND `type` = 'buy' AND payment_method like 'Cashapp' ";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $cashapp = $row['cash_app'];
                                
                                
                                $sql = "SELECT COUNT(*) as coinbase, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >=DATE_SUB(NOW(), INTERVAL 1 YEAR) AND `type` = 'buy' AND payment_method like 'Coinbase'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $coinbase = $row['coinbase'];
                                
                                $sql = "SELECT COUNT(*) as balances, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR) AND `type` = 'buy' AND payment_method like 'Balance'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $balance = $row['balances'];

                            }elseif($type == "all"){

                                $sql = "SELECT COUNT(*) as total_orders, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND `type` = 'buy'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_orders = $row['total_orders'];
                                $total_orders_amount = $row['total_amount'];

                                $sql = "SELECT COUNT(*) as total_orders, sum(amount) as total_amount FROM orders WHERE `status` = '2' AND `type` = 'deposit'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_deposits = $row['total_orders'];
                                $total_deposits_amount = $row['total_amount'];

                                $sql = "SELECT COUNT(*) as total_tickets FROM tickets";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_tick = $row['total_tickets'];

                                $sql = "SELECT COUNT(*) as total_review FROM reviews";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_reviews = $row['total_review'];

                                $sql = "SELECT COUNT(*) as total_user FROM users";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_users = $row['total_user'];

                                $sql = "SELECT COUNT(status) as closed_ticket FROM tickets WHERE status = 0;";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $closed_tickets = $row['closed_ticket'];

                                $sql = "SELECT COUNT(*) as active_ticket FROM tickets WHERE status = 1;";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_tickets_active = $row['active_ticket'];
                                
                                 $sql = "SELECT COUNT(*) as cash_app, sum(amount) as total_amount FROM orders WHERE `status` = '2'  AND `type` = 'buy' AND payment_method like 'Cashapp' ";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $cashapp = $row['cash_app'];
                                
                                
                                $sql = "SELECT COUNT(*) as coinbase, sum(amount) as total_amount FROM orders WHERE `status` = '2'  AND `type` = 'buy' AND payment_method like 'Coinbase'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $coinbase = $row['coinbase'];
                                
                                $sql = "SELECT COUNT(*) as balances, sum(amount) as total_amount FROM orders WHERE `status` = '2'  AND `type` = 'buy' AND payment_method like 'Balance'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $balance = $row['balances'];

                            }

                            //if any empty set to 0
                            if(empty($total_orders)){
                                $total_orders = 0;
                            }
                            if(empty($total_orders_amount)){
                                $total_orders_amount = 0;
                            }
                            if(empty($total_deposits)){
                                $total_deposits = 0;
                            }
                            if(empty($total_deposits_amount)){
                                $total_deposits_amount = 0;
                            }

                            if (empty($total_tick)) {
                                $total_tick = 0;
                            }

                            if (empty($total_reviews)) {
                                $total_reviews = 0 ;
                            }

                            if (empty($total_tickets))
                             {
                                $total_tickets = 0;
                             }
                            

                             if (empty( $total_tickets_active))
                             {
                                $total_tickets_active = 0;
                             }
                            ?>

                        <div class="row">
                            <div class="col-lg-4 col-md-6 col-lg-4">
                                <div class="total_ordr_placed_wrap">
                                    <h5 class="text-danger mt-0 mb-1">Orders Placed</h5>
                                    <p class="text-white"><?php echo $total_orders; ?></p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-lg-4">
                                <div class="total_ordr_placed_wrap">
                                    <h5 class="text-primary mt-0 mb-1">Orders Value</h5>
                                    <p class="text-white">$<?php echo $total_orders_amount; ?></p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-lg-4">
                                <div class="total_ordr_placed_wrap">
                                    <h5 class="text-success mt-0 mb-1">Total Deposits</h5>
                                    <p class="text-white">
                                        $<?php echo $total_deposits_amount; ?></p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-lg-4">
                                <div class="total_ordr_placed_wrap">
                                    <h5 class="text-light mt-0 mb-1">Reviews Count</h5>
                                    <p class="text-white"><?php echo $total_reviews; ?></p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-lg-4">
                                <div class="total_ordr_placed_wrap">
                                    <h5 class="text-danger mt-0 mb-1">Total Users</h5>
                                    <p class="text-white">
                                        <?php echo $total_users; ?></p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-lg-4">
                                <div class="total_ordr_placed_wrap">
                                    <h5 class="text-info mt-0 mb-1">Tickets Count</h5>
                                    <p class="text-white"><?php echo $total_tick; ?></p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-lg-4" >
                                <div class="total_ordr_placed_wrap">
                                    <h5 class="text-primary mt-0 mb-1">Tickets Closed</h5>
                                    <p class="text-white"><?php echo $total_tickets; ?></p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-lg-4">
                                <div class="total_ordr_placed_wrap">
                                    <h5 class="text-primary mt-0 mb-1">Tickets Active</h5>
                                    <p class="text-white"><?php echo $total_tickets_active; ?></p>
                                </div>
                            </div>
                            
                            <div class="col-lg-4 col-md-6 col-lg-4" >
                                <div class="total_ordr_placed_wrap">
                                    <h5 class="text-info mt-0 mb-1">Cashapp Payments</h5>
                                    <p class="text-white"><?php echo $cashapp; ?></p>
                                </div>
                            </div>
                            
                            <div class="col-lg-4 col-md-6 col-lg-4">
                                <div class="total_ordr_placed_wrap">
                                    <h5 class="text-info mt-0 mb-1">Coinbase Payments</h5>
                                    <p class="text-white"><?php echo $coinbase; ?></p>
                                </div>
                            </div>
                            
                             <div class="col-lg-4 col-md-6 col-lg-4">
                                <div class="total_ordr_placed_wrap">
                                    <h5 class="text-info mt-0 mb-1">Balance Payments</h5>
                                    <p class="text-white"><?php echo $balance; ?></p>
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







</body>

</html>