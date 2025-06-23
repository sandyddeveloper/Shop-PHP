<?php
session_start();

require_once './server/db.php';
require_once './comp/functions.php';
require_once './include/payments.php';



if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    $guest = true;
    if(!isset($_GET['email'])){
        $error = "Invalid email";
        $error = urlencode($error);
        header("location: ../user/cart.php?error=".$error);
        exit();
    }
    $email = mysqli_real_escape_string($conn, $_GET['email']);
    if(!$error = validateEmailWithReturn($email)){
        $error = urlencode($error);
        header("location: ../user/cart.php?error=".$error);
        exit();
    }
}else{
    $id = $_SESSION["id"];
    $username = $_SESSION["username"];
    $role = intval(getUserData($id, "role"));
    $guest = false;
}






if(!isset($_GET['id'])){
    $error = "Invalid product";
    $error = urlencode($error);
    header("location: ../user/myorders.php?error=".$error);
    exit();
}


$order_id = mysqli_real_escape_string($conn, $_GET['id']);
if(!$guest){
    $sql = "SELECT * FROM `orders` WHERE `id` = '$order_id'";
    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result) == 0){
        $error = "Invalid order";
        $error = urlencode($error);
        header("location: ../user/myorders.php?error=".$error);
        exit();
    }
}else{
    $sql = "SELECT * FROM `orders` WHERE `id` = '$order_id' AND `email` = '$email'";
    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result) == 0){
        $error = "Invalid order";
        $error = urlencode($error);
        header("location: ../user/cart.php?error=".$error);
        exit();
    }
}


$order = mysqli_fetch_assoc($result);
$user_id = $order['user_id'];
if(!$guest && $user_id != $id && $role != 1 && $role != 2){
    $error = "Invalid order";
    $error = urlencode($error);
    header("location: ../user/myorders.php?error=".$error);
    exit();
}


if($order['status'] == 0){
    $status = '<span class="badge bg-danger">Unknown</span>';
}elseif($order['status'] == 1){
    $status = '<span class="badge bg-warning">Pending</span>';
}elseif($order['status'] == 2){
    $status = '<span class="badge bg-success">Completed</span>';
}elseif($order['status'] == 3){
    $status = '<span class="badge bg-danger">Failed</span>';
}elseif($order['status'] == 4){
    $status = '<span class="badge bg-info">New</span>';
}



if(isset($_GET['act'])){
    if($role != 1 && $role != 2){
        $error = "Invalid action";
        $error = urlencode($error);
        header("location: order.php?id=".$order_id."&error=".$error);
        exit();
    }

    if($_GET['act'] == "replace"){
        if($order['type'] != 'buy'){
            $error = "Invalid action";
            $error = urlencode($error);
            header("location: order.php?id=".$order_id."&error=".$error);
            exit();
        }


        $prod_id = mysqli_real_escape_string($conn, $_POST['prod_id']);
        $amount = mysqli_real_escape_string($conn, $_POST['amount']);




        $status = replaceOrder($order_id, $prod_id, $amount);
        $code = $status["code"];
        if($code){
            $sql = "UPDATE `orders` SET `replaced_by` = '$username' WHERE `id` = '$order_id'";
            $result = mysqli_query($conn, $sql);
            $success = "Order delivered";
            $success = urlencode($success);
            
            header("location: order.php?id=".$order_id."&success=".$success);
            exit();
        }else{
            $error = "Failed to deliver order due to ".$status["error"];
            $error = urlencode($error);
            header("location: order.php?id=".$order_id."&error=".$error);
            exit();
        }

    }elseif ($_GET['act'] == "fulfill") {
    $sql = "SELECT * FROM `orders` WHERE `id` = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    
    $stmt->bind_param("i", $order_id);
    
    if (!$stmt->execute()) {
        die("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result) {
        $order = $result->fetch_assoc();
    } else {
        $order = null;
    }
    
    $stmt->close();

    // Check if the order belongs to the current user
    if ($order['user_id'] == $id) {
        $error = "You cannot fulfill your own orders!";
        $error = urlencode($error);
        header("location: order.php?id=".$order_id."&error=".$error);
        exit();
    }

    // Continue with the rest of your fulfillment logic
    if ($order['type'] != 'buy') {
        $error = "Invalid action";
        $error = urlencode($error);
        header("location: order.php?id=".$order_id."&error=".$error);
        exit();
    }

    if ($order['status'] == 2) {
        $error = "Order already delivered";
        $error = urlencode($error);
        header("location: order.php?id=".$order_id."&error=".$error);
        exit();
    } else {
       
        $sql = "UPDATE `orders` SET `status` = ? WHERE `id` = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }
        
        $statuss = 2;
        $stmt->bind_param("ii", $statuss, $order_id);
        
        if (!$stmt->execute()) {
            die("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }
        
        $stmt->close();
        
        $status = deliverOrder($order_id);
        $code = $status["code"];
        if ($code) {
            $success = "Order fulfilled";
            $success = urlencode($success);
            header("location: order.php?id=".$order_id."&success=".$success);
            exit();
        } else {
            $error = "Failed to fulfill order due to ".$status["error"];
            $error = urlencode($error);
            header("location: order.php?id=".$order_id."&error=".$error);
            exit();
        }
    }
    }
    elseif ($_GET['act'] == "refund_balance") {
        if ($order['user_id'] != 0) {
            // User is not a guest
    
            // Fetch the user_id and amount from the order
            $user_id = $order['user_id'];
            $amount = $order['amount'];
    
            if ($order['refunded'] == 0) {
                $order_id2 = $order['id'];
    
                $updateUserBalance = "UPDATE users SET bal = bal + ? WHERE id = ?";
                $stmt = $conn->prepare($updateUserBalance);
                $stmt->bind_param("di", $amount, $user_id);
                $stmt->execute();
                $stmt->close();
    
                $changestatus = "UPDATE `orders` SET refunded = 1 WHERE id = ?";
                $stmt = $conn->prepare($changestatus);
                
                if ($stmt === false) {
                    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                }
                
                $stmt->bind_param("i", $order_id2);
                $stmt->execute();
                $stmt->close();
    
                // Success message
                $success = "Balance refunded successfully";
                $success = urlencode($success);
                header("location: order.php?id=" . $order_id . "&success=" . $success);
                exit();
            } else {
                // Order already refunded
                $error = "Order has been already Refunded!";
                $error = urlencode($error);
                header("location: order.php?id=" . $order_id . "&error=" . $error);
                exit();
            }
        } else {
            // Guest checkout, cannot refund balance
            $error = "Guest checkout, cannot refund balance";
            $error = urlencode($error);
            header("location: order.php?id=" . $order_id . "&error=" . $error);
            exit();
        }
    }    
}

if($order['payment_method'] == "Cashapp" && $order['status'] == 1 && !empty($order['uqid'])){

    $status = '<span class="badge bg-warning">Waiting for approval</span>';

    $user = $order['uqid'];
    $note = $order['id'].'-'.$order['cashapp_note'];

    $cashapp_conf_resp = imap_scaner_new($order['amount'], "$", $note, $user);

    if($cashapp_conf_resp == true){
        $sql = "UPDATE `orders` SET `status` = '2' WHERE `id` = '$order_id'";
        $result = mysqli_query($conn, $sql);

        if($order['type'] == 'deposit'){
            $amount = $order['amount'];
            $sql = "UPDATE `users` SET `bal`=`bal`+'$amount' WHERE `id`='$id';";
            $result = mysqli_query($conn, $sql);

            $success = "Payment received! Your balance has been updated successfully.";
            $success = urlencode($success);
            header("location: order.php?id=".$order_id."&success=".$success);
            exit();

        }else{
            deliverOrder($order_id);

            $success = "Payment received! Your order has been delivered successfully.";
            $success = urlencode($success);
            $link = (!$guest) ? "location: order.php?id=".$order_id."&success=".$success : "location: order.php?id=".$order_id."&success=".$success."&email=".$email;
            header($link);
            exit();

        }

    }
    
}elseif($order['payment_method'] == "Cashapp" && $order['status'] != "2"){
    $note = $order['id'].'-'.$order['cashapp_note'];
    $cashapp_resp = imap_scaner($order['amount'], "$", $note);

    $cashapp_status = $cashapp_resp['code'];
    if($cashapp_status == true){
        $user = trim($cashapp_resp['user']);

        $sql = "UPDATE `orders` SET `uqid` = '$user' WHERE `id` = '$order_id'";
        $result = mysqli_query($conn, $sql);
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
    <?php include 'comp/header.php'; ?>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <title><?php echo $shop_title; ?> - Order</title>
    <style>
        
        .hoveing:hover {
            color: red;
        }
    </style>
    
    
      <style>
         body {
            overflow-x: hidden;
        }
    </style>
</head>

<body>

    <main class="main" id="top">

        <?php 
        
        include 'comp/nav.php';

        ?>
        <div class="dashboard_main_sec">

            <div class="container">

                <div class="row">

                    <?php 
                    
                    if($guest){
                        echo '<div class="col-3">
                                <div class="why_add_balnce_wrap mt-0">
                                    <h6>Welcome, Guest</h6>
                                    <div class="heading_btn">
                                        <a href="login.php" class="nav-link w-100 mb-3 text-center">  Login</a>
                                        <a href="register.php" class="nav-link w-100 mb-3 text-center">  Register</a>
                                    </div>
                                </div>
                        
                            </div>';
                    }else{
                        include 'comp/subnav.php'; 
                    }
                    ?>



                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-lg-12">
                               <div class="dashboard_main_head text-center">
                                    <h5>Order #<?php echo $order['id']; ?></h5>
                                    <p>View and manage your order</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">	

                        
                            <div class="user_id_wrap">
                                    <div class="media">
                                        <div class="box-icon" style="background-color: #e02844;"> <i class="fa fa-id-card"></i> </div>
                                        <div class="media-body">
                                            <p>Order ID</p>
                                            <h6>#<?php echo $order['id']; ?> </h6>
                                        </div>
                                    </div>
                                    
                                    <div class="media">
                                        <div class="box-icon" style="background-color: #e02844;"> <i class="fas fa-coins"></i> </div>
                                        <div class="media-body">
                                            <p>Amount</p>
                                            <h6>$<?php echo $order['amount']; ?></h6>
                                        </div>
                                    </div>
                                    <div class="media">
                                        <div class="box-icon" style="background-color: #e02844;"> <i class="fa fa-calendar"></i> </div>
                                        <div class="media-body">
                                            <p>Date</p>
                                            <h6><?php echo date("j M, G:i", strtotime($order['created_at'])); ?></h6>
                                        </div>
                                    </div>
                                    <div class="media">
                                        <div class="box-icon" style="background-color: #e02844;"> <i class="fa fa-check-circle"></i> </div>
                                        <div class="media-body">
                                            <p>Status</p>
                                            <h6><?php echo $status; ?></h6>
                                        </div>
                                    </div>
                                    <div class="media">
                                        <div class="box-icon" style="background-color: #e02844;"> <i class="fa fa-credit-card"></i> </div>
                                        <div class="media-body">
                                            <p>Payment Method</p>
                                            <h6><?php echo $order['payment_method']; ?></h6>
                                        </div>
                                    </div>
                                     <div class="media">
                                        <div class="box-icon" style="background-color: #e02844;"> <i class="fa fa-truck"></i> </div>
                                       <div class="media-body">
    <p>Delivery Key</p>
    <h6 style="display: none !important" id="orderId"><?php echo htmlspecialchars($order['get_order_id']); ?></h6>
    <button onclick="copyOrderId()" style="color: #FFF">Click here to copy</button>
</div>
                                    </div>
                                    
                                    <?php
                                    
                                    if($order['type'] == 'buy'){
                                    

                                    ?>
                                    <div class="media">
                                        <div class="box-icon" style="background-color: #e02844;"> <i class="fa fa-envelope"></i> </div>
                                        <div class="media-body">
                                            <p>Email Address</p>
                                            <h6><?php echo $order['email']; ?></h6>
                                        </div>
                                    </div>
                                    <div class="media">
                                        <div class="box-icon" style="background-color: #e02844;"> <i class="fa fa-truck"></i> </div>
                                        <div class="media-body">
                                    
 <?php
                                                $counter = 0;
                                                $prodArr = json_decode($order['prod_ids'], true);
                                                foreach($prodArr as $prod){
                                                    $prodId = $prod['prod_id'];
                                                    $prodQuantity = $prod['quantity'];
                                                    $prodGoods = $prod['goods'];
                                                    $prodTitle = $prod['title'];
                                                    $counter++;


                                                    echo '<p>Product '.$counter.':</p><h6>x'.$prodQuantity.' '.$prodTitle.'</h6>';
                                                }
                                            ?>



                                        </div>
                                        <?php 
                               if ($order['status'] == 2)
                               {
                                echo '<button id="reviewRedirectBtn" class="text-white bg-purple-700 hover:bg-purple-800 focus:outline-none focus:ring-4 focus:ring-purple-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mb-2 dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-900">Review</button>';
                               }
                               else 
                               {
                                echo ' <button id="reviewRedirectBtn" class="cursor-not-allowed text-white bg-purple-700 hover:bg-purple-800 focus:outline-none focus:ring-4 focus:ring-purple-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mb-2 dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-900" disabled>Review</button>';

                               }
                           ?>
                                        
                                    </div>

                                    

                                    

                                    <?php
                                    }
                                    

                                    ?>

                                </div>


                                <?php 



                                    if(!$guest && $order['type'] == "buy" && $role == 1 || $role == 2){ ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="dashboard_main_head">
                                                    <h5>Admin Section</h5>
                                                    <p>Admin can change order settings here.</p>
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
                                            <div class="col-lg-12">
                                                <div class="user_id_wrap">
                                                    <div class="media">
                                                        <div class="box-icon" style="background-color: #e02844;"> <i class="fa fa-globe"></i> </div>
                                                        <div class="media-body">
                                                            <p>IP</p>
                                                            <h6><?php echo $order['ip']; ?></h6>
                                                        </div>
                                                    </div>

                                                    <div class="media">
                                                        <div class="box-icon" style="background-color: #e02844;"> <i class="fa fa-check"></i> </div>
                                                        <div class="media-body">
                                                            <p>Fulfill order</p>
                                                            <h6><a href="order.php?id=<?php echo $order['id']; ?>&act=fulfill" class="link-no-effect text-danger">[Fulfill]</a></h6>
                                                        </div>
                                                    </div>
                                                    
                                                   <div class="media">
    <div class="box-icon" style="background-color: #e02844;"> <i class="fa fa-check"></i> </div>
    <div class="media-body">
        <p>Refund balance</p>
        <h6><a href="order.php?id=<?php echo $order['id']; ?>&act=refund_balance" class="link-no-effect text-danger">[Refund Balance]</a></h6>
    </div>
</div>

                                                </div>

                                                <div class="order_details_wrapp p-2">
                                                    <div class="container">                                     
                                                        <div class="row">
                                                            <form class="row m-0 w-100" action="order.php?id=<?php echo $order['id']; ?>&act=replace" method="POST">
                                                                <div class="col-md-12 mb-3 mt-3">
                                                                    <label for="" class="form-label text-white">Product to replace</label>
                                                                    <select class="form-control bg-dark text-white" aria-label="Default select example" name="prod_id" id="prod_id">
                                                                        <option selected>Open this select menu</option>
                                                                        <?php
                                                                            $prodArr = json_decode($order['prod_ids'], true);
                                                                            foreach($prodArr as $prod){
                                                                                $prodId = $prod['prod_id'];
                                                                                $prodQuantity = $prod['quantity'];
                                                                                $prodGoods = $prod['goods'];
                                                                                echo '<option value="'.$prodId.'">'.$prod['title'].'</option>';
                                                                            }
                                                                        ?>
                                                                    </select>

                                                                </div>

                                                                <div class="col-md-12 mb-3">
                                                                    <label for="" class="form-label text-white">Amout to replace</label>
                                                                    <input type="number" class="form-control bg-dark text-white" name="amount">

                                                                </div>
                                                                <div class="col-md-12 mb-3">
                                                                    <button class="btn btn-danger w-100" href="<?=$order['link'];?>" target="_blank" >Replace</button>
                                                                </div>

                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                <?php 
                                    } ?>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="order_details_wrapp p-2">
                                    <div class="container">

                                        <?php
  
                                            if($order['type'] == "buy"){
                                        
                                                if($order['status'] == "2"){
                                             
                                                    if($guest){
                                                        echo '<div class="head_notify_box mb-3 mt-3" role="alert"><p>Your order has been delivered on email.</p></div>';

                                        
}else{
    $prodArr = json_decode($order['prod_ids'], true);

    foreach ($prodArr as $prod) {
        $prodId = $prod['prod_id'];
        $prodQuantity = $prod['quantity'];
        $prodGoods = $prod['goods'];

        if (is_array($prodGoods)) {
            $rows = count($prodGoods) + 1;

            echo '
            <div class="row deliv">
                <div class="col-md-12 mb-3 mt-3">
                    <span class="fa fa-truck mr-2 text-danger"></span>
                    <span class="text-white">' . $prod['title'] . ' x' . $prodQuantity . '</span>
                </div>
                <div class="col-md-12 mb-3">
                    <textarea class="form-control bg-dark text-white" rows="' . $rows . '" readonly>';
                    foreach ($prodGoods as $good) {
                        echo $good . "\n";
                    }
                    echo '</textarea>
                </div>
            </div>';
        } else {
            echo '<div class="row deliv">
                    <div class="col-md-12 mb-3 mt-3">
                        <span class="fa fa-truck mr-2 text-danger"></span>
                        <span class="text-white">' . $prod['title'] . ' x' . $prodQuantity . '</span>
                    </div>
                    <div class="col-md-12 mb-3">
                        <p class="text-white">No goods available. <a class="hoveing" href="support.php">Contact support</a></p>  
                    </div>
                </div>';
        }
    }


                                                        
                                                           
                                                
                                                        
                                                                    //read json data from prod_ids

                                                        if(!empty($order['replace_order'])){
                                                            $counter = 0;

                                                            $prodArr = json_decode($order['replace_order'], true);

                                                            foreach($prodArr as $replacement_order){
                                                                $counter++;
                                                                
                                                                foreach($replacement_order as $prod){
                                                                    $prodId = $prod['prod_id'];
                                                                    $prodQuantity = $prod['quantity'];
                                                                    $prodGoods = $prod['goods'];
                                                                    $rows = count($prodGoods) + 1;
                                                                    echo '
                                                                    
                                                                    <div class="row deliv">
                                                                        <div class="col-md-12 mb-3">
                                                                            <span class="fa fa-truck mr-2 text-danger"></span>
                                                                            <span class="text-white">'.$prod['title'].' x'.$prodQuantity.' - Replacement '.$counter.' By '.$order['replaced_by'].'</span>
                                                                        </div>

                                                                        <div class="col-md-12 mb-3">
                                                                            <textarea class="form-control bg-dark text-white" rows="'.$rows.'" readonly>';
                                                                            foreach($prodGoods as $good){
                                                                                echo $good."\n";
                                                                            }
                                                                            echo '</textarea>
                                                                        </div>
                                                                    </div>

                                                                    ';
                                                                }
                                                            }
                                                        }

                                                    }

                                                //else status not 2
                                                }else{
                                                    if($order['payment_method'] == "Cashapp"){
                                                        $tag = getData("1", "cashapp_cashtag", "site_settings"); ?>

                                                        <div class="row">

                                                            <div class="head_notify_box mb-4" role="alert">
                                                                <p>Write the following note when sending the payment. If you don't, your order will not be delivered!</p>
                                                            </div>
                                                                
                                                            <div class="col-md-12 mb-3">
                                                                <label for="" class="form-label text-white">Cashapp Tag</label>
                                                                <input type="text" class="form-control bg-dark text-white" readonly value="<?=$tag;?>">
                                                            </div>

                                                            <div class="col-md-12 mb-3">
                                                                
                                                                <label for="" class="form-label text-white">Cashapp Note</label>
                                                                <input type="text" class="form-control bg-dark text-white" readonly value="<?=$note;?>">
                                                            </div>

                                                            <div class="col-md-12 d-flex justify-content-center">
                                                                <img src="https://api.cash-payments.io/cash-code/<?=$tag;?>/<?=$order['amount'];?>/<?=$order['cashapp_note'];?>" <="" center="" style="padding: 10px;background: white;border-radius: 10px;">
                                                            </div>


                                                            <div class="col-md-12 mb-3">
                                                                <label for="" class="form-label text-white">Amount to send</label>
                                                                <input type="text" class="form-control bg-dark text-white" readonly value="<?=$order['amount'];?>">
                                                            </div>
                                                        </div>
                                                        <?php
                                                    }else{ ?>
                                                        <div class="row">
                                                            <div class="col-md-12 mb-3 mt-3">
                                                                <a class="btn btn-danger w-100" href="<?=$order['link'];?>" target="_blank" >Pay Now<i class="fas fa-wallet" style="margin-left: 10px;"></i></a>
                                                            </div>
                                                        </div>
                                                        <?php 

                                                    }

                                                }
                                            //else type deposit
                                            }else{
                                                if($order['status'] == "2"){
                                                    
                                                    echo '<div class="head_notify_box mb-3 mt-3" role="alert"><p>Deposit is now confirmed. Balance has been updated.</p></div>';


                                                //else status not 2
                                                }else{
                                                    if($order['payment_method'] == "Cashapp"){
                                                        $tag = getData("1", "cashapp_cashtag", "site_settings"); ?>

                                                        <div class="row">

                                                            <div class="head_notify_box mb-4" role="alert">
                                                                <p>Write the following note when sending the payment. If you don't, your order will not be delivered!</p>
                                                            </div>
                                                                
                                                            <div class="col-md-12 mb-3">
                                                                <label for="" class="form-label text-white">Cashapp Tag</label>
                                                                <input type="text" class="form-control bg-dark text-white" readonly value="<?=$tag;?>">
                                                            </div>

                                                            <div class="col-md-12 mb-3">
                                                                
                                                                <label for="" class="form-label text-white">Cashapp Note</label>
                                                                <input type="text" class="form-control bg-dark text-white" readonly value="<?=$note;?>">
                                                            </div>

                                                            <div class="col-md-12 d-flex justify-content-center">
                                                                <img src="https://api.cash-payments.io/cash-code/<?=$tag;?>/<?=$order['amount'];?>/<?=$order['cashapp_note'];?>" <="" center="" style="padding: 10px;background: white;border-radius: 10px;">
                                                            </div>


                                                            <div class="col-md-12 mb-3">
                                                                <label for="" class="form-label text-white">Amount to send</label>
                                                                <input type="text" class="form-control bg-dark text-white" readonly value="<?=$order['amount'];?>">
                                                            </div>
                                                        </div>
                                                        <?php
                                                    }else{ ?>
                                                        <div class="row">
                                                            <div class="col-md-12 mb-3 mt-3">
                                                                <a class="btn btn-danger w-100" href="<?=$order['link'];?>" target="_blank" >Pay Now<i class="fas fa-wallet" style="margin-left: 10px;"></i></a>
                                                            </div>
                                                        </div>
                                                        <?php 

                                                    }

                                                }
                                            }
                                            ?>

                                    </div>


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

    <script>
    document.getElementById('reviewRedirectBtn').addEventListener('click', function() {
        window.location.href = 'review.php?order_id=<?php echo $order['id']; ?>';
    });


        </script>


<script>
function copyOrderId() {
    var orderIdText = document.getElementById('orderId').innerText;
    
    var tempTextArea = document.createElement('textarea');
    tempTextArea.value = orderIdText;
    
    document.body.appendChild(tempTextArea);
    
    tempTextArea.select();
    tempTextArea.setSelectionRange(0, 99999); 
    document.execCommand('copy');
    
    document.body.removeChild(tempTextArea);
}
</script>



</body>

</html>

