<?php
session_start();

require_once('server/db.php');
require_once 'comp/functions.php';



$stocks = array();
//get all stocks
$sql = "SELECT `prod_id`, COUNT(id) as num FROM `stock` GROUP BY `prod_id` ORDER BY `prod_id` ASC;";
$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)){
    $prod_id = $row['prod_id'];
    $stocks[$prod_id] = $row['num'];
}


if($_SERVER['REQUEST_METHOD'] == 'POST'){

    if (isset($_SESSION["loggedin"])){

        $id = $_SESSION["id"];
        $username = $_SESSION["username"];
        $role = intval(getUserData($id, "role"));
        $bal = getUserData($id, "bal");
        $email = getUserData($id, "email");


        if(isset($_SESSION['cart']) && is_array($_SESSION['cart']) && count($_SESSION['cart']) > 0){
            $items_count = count($_SESSION['cart']);
            $purchaseArr = array();

            $total_cart = count($_SESSION['cart']);
            $total_cost = 0;
            foreach($_SESSION['cart'] as $prod_id => $array){
                if($total_cart == 0){
                    $error = "Cart is empty";
                    $error = urlencode($error);
                    header("location: ./user/cart.php?error=".$error);
                    exit();
                }
                $prod_id = intval($prod_id);
                $quantity = intval($array['quantity']);


                $sql = "SELECT * FROM `prods` WHERE `id` = '$prod_id'";
                $result = mysqli_query($conn, $sql);
                if(mysqli_num_rows($result) == 0){
                    $error = "Invalid product";
                    $error = urlencode($error);
                    //remove item from cart
                    unset($_SESSION['cart'][$prod_id]);
                    $total_cart = $total_cart - 1;
                    continue;
                }


                //check stock
                if(!isset($stocks[$prod_id]) || $stocks[$prod_id] < $quantity){
                    $error = "Out of stock";
                    $error = urlencode($error);
                    //remove item from cart
                    unset($_SESSION['cart'][$prod_id]);
                    $total_cart = $total_cart - 1;
                    continue;
                }

                $prod = mysqli_fetch_assoc($result);
                $price = $prod['price'];
                $title = $prod['title'];

                $min = $prod['min'];

                if($quantity < $min){
                    $error = "Minimum quantity is $min";
                    $error = urlencode($error);
                    header("location: ./user/cart.php?error=".$error);
                    exit();
                }


                $cost = $quantity * $price;
                $tmp = array('prod_id' => $prod_id, 'quantity' => $quantity, 'goods' => '', 'title' => $title);
                $purchaseArr[$prod_id] = $tmp;
                

                $total_cost = $total_cost + $cost;

                

            }

            $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
            $coupon = mysqli_real_escape_string($conn, $_POST['coupon']);
            $crypto_type = mysqli_real_escape_string($conn, $_POST['crypto_type']);

            $discount = 0;

            $sql = "SELECT * FROM `coups` WHERE `code` = '$coupon' AND `status` = 1";
            $result = mysqli_query($conn, $sql);
            if(mysqli_num_rows($result) != 0){
                $coupon = mysqli_fetch_assoc($result);
                $discount = $coupon['discount'];
            }

            $payments = array("Balance", "Coinbase", "Sellix", "Cashapp", "Poof");
            if(!in_array($payment_method, $payments)){
                $error = "Invalid payment method";
                $error = urlencode($error);
                header("location: ./user/cart.php?error=".$error);
                exit();
            }

            $sql = "SELECT * FROM `site_settings` WHERE `id`='1';";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);

            $coinbase_api = $row["coinbase_api"];
            $coinbase_secret = $row["coinbase_secret"];



            $total_cost = $total_cost - ($total_cost * ($discount / 100));

            $prod_id = json_encode($purchaseArr);

            if($payment_method == "Balance"){
                if($bal < $total_cost){
                    $error = "Insufficient balance";
                    $error = urlencode($error);
                    header("location: ./user/cart.php?error=".$error);
                    exit();
                }

                $newBal = $bal - $total_cost;
                $sql = "UPDATE `users` SET `bal` = '$newBal' WHERE `id` = '$id'";
            
                $result = mysqli_query($conn, $sql);
            
                $ip = $_SERVER['REMOTE_ADDR'];
                 $bytes = openssl_random_pseudo_bytes(32);
                $get_order_id = bin2hex($bytes);
            
                $sql = "INSERT INTO `orders` (`user_id`, `prod_ids`, `payment_method`, `status`, `amount`, `email`, `type`, `ip`, `get_order_id`) VALUES ('$id', '$prod_id', '$payment_method', '2', '$total_cost', '$email', 'buy', '$ip', '$get_order_id');";

                $result = mysqli_query($conn, $sql);
                //get inserted order id
                $order_id = mysqli_insert_id($conn);

            
                deliverOrder($order_id);

            
                header("location: order.php?id=".$order_id);
                exit();


            }else if($payment_method == "Coinbase"){
                $post = array(
                    "name" => "Buy ".$items_count." items",
                    "description" => "Buy ".$items_count." items",
                    "local_price" => array(
                        'amount' => $total_cost,
                        'currency' => 'USD'
                    ),
                    "pricing_type" => "fixed_price",
                    "metadata" => array(
                        'name' => $username
                    )
                );
        
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.commerce.coinbase.com/charges');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
                
                $headers = array();
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'X-Cc-Api-Key: '.$coinbase_api;
                $headers[] = 'X-Cc-Version: 2018-03-22';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $response = curl_exec($ch);
                curl_close($ch);
                
                
                $bytes = openssl_random_pseudo_bytes(32);
                $get_order_id = bin2hex($bytes);

            
                $response = json_decode($response, true);
                $uqid = $response['data']['id'];
                $url = $response['data']['hosted_url'];
                $ip = $_SERVER['REMOTE_ADDR'];

               $sql = "INSERT INTO `orders` (`user_id`, `prod_ids`, `payment_method`, `status`, `amount`, `email`, `link`, `uqid`, `type`, `ip`, `get_order_id`) VALUES ('$id', '$prod_id', '$payment_method', '4', '$total_cost', '$email', '$url', '$uqid', 'buy', '$ip', '$get_order_id');";
               $result = mysqli_query($conn, $sql);
                

                header("location: ".$url);
                exit();

            }elseif ($payment_method == "Poof") {
                $poof_api_key = 'f-PM4WCQY6R6hHYoGz4YqA';
                $poof_url = 'https://www.poof.io/api/v2/create_invoice';
            
                $post = array(
                    "amount" => $total_cost,
                    "crypto" => $crypto_type
                );
            
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $poof_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
                
                $headers = array();
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Authorization: ' . $poof_api_key;
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                
                $response = curl_exec($ch);
                if ($response === false) {
                    curl_close($ch);
                    $error = "Poof API connection failed";
                    header("location: ./user/cart.php?error=" . urlencode($error));
                    exit();
                }
                curl_close($ch);
            
                $response = json_decode($response, true);
            
                if (isset($response['payment_link']) && isset($response['payment_id'])) {
                    $payment_link = $response['payment_link'];
                    $payment_id = $response['payment_id'];
            
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $bytes = openssl_random_pseudo_bytes(32);
                    $get_order_id = bin2hex($bytes);
            
                    $sql = "INSERT INTO `orders` (`user_id`, `prod_ids`, `payment_method`, `status`, `amount`, `email`, `link`, `uqid`, `type`, `ip`, `get_order_id`) VALUES ('$id', '$prod_id', '5', '4', '$total_cost', '$email', '$payment_link', '$payment_id', 'buy', '$ip', '$get_order_id');";
                    $result = mysqli_query($conn, $sql);
                    if (!$result) {
                        $error = "Database error: " . mysqli_error($conn);
                        header("location: ./user/cart.php?error=" . urlencode($error));
                        exit();
                    }
            
                    header("Location: " . $payment_link);
                    exit();
                } else {
                    $error = "Invalid response from Poof API";
                    header("location: ./user/cart.php?error=" . urlencode($error));
                    exit();
                }
            }
            else if($payment_method == "Cashapp"){
                
                $note = cashapp_note();

                $ip = $_SERVER['REMOTE_ADDR'];
                $sql = "INSERT INTO `orders` (`user_id`, `prod_ids`, `payment_method`, `status`, `amount`, `email`, `link`, `cashapp_note`, `type`, `ip`) VALUES ('$id', '$prod_id', '$payment_method', '1', '$total_cost', '$email', 'order', '$note', 'buy', '$ip');";
                $result = mysqli_query($conn, $sql);
        
                //get inserted id
                $order_id = mysqli_insert_id($conn);
        
                header("location: order.php?id=".$order_id);
                exit();
            }






            
        }else{
            $error = "Cart is empty";
            $error = urlencode($error);
            header("location: ./user/cart.php?error=".$error);
            exit();
        }
        
        
    }else{
    
        //process order not logged in users
        $email = mysqli_real_escape_string($conn, $_POST['email']);


        $id = 'guest';
        $username = 'guest';
        $bal = 0;

        if(!validateEmailWithReturn($email)){
            $error = "Invalid email";
            $error = urlencode($error);
            header("location: ./user/cart.php?error=".$error);
            exit();
        }


        if(isset($_SESSION['cart']) && is_array($_SESSION['cart']) && count($_SESSION['cart']) > 0){
            $items_count = count($_SESSION['cart']);
            $purchaseArr = array();

            $total_cart = count($_SESSION['cart']);
            $total_cost = 0;
            foreach($_SESSION['cart'] as $prod_id => $array){
                if($total_cart == 0){
                    $error = "Cart is empty";
                    $error = urlencode($error);
                    header("location: ./user/cart.php?error=".$error);
                    exit();
                }
                $prod_id = intval($prod_id);
                $quantity = intval($array['quantity']);


                $sql = "SELECT * FROM `prods` WHERE `id` = '$prod_id'";
                $result = mysqli_query($conn, $sql);
                if(mysqli_num_rows($result) == 0){
                    $error = "Invalid product";
                    $error = urlencode($error);
                    //remove item from cart
                    unset($_SESSION['cart'][$prod_id]);
                    $total_cart = $total_cart - 1;
                    continue;
                }


                //check stock
                if(!isset($stocks[$prod_id]) || $stocks[$prod_id] < $quantity){
                    $error = "Out of stock";
                    $error = urlencode($error);
                    //remove item from cart
                    unset($_SESSION['cart'][$prod_id]);
                    $total_cart = $total_cart - 1;
                    continue;
                }

                $prod = mysqli_fetch_assoc($result);
                $price = $prod['price'];
                $title = $prod['title'];

                $min = $prod['min'];

                if($quantity < $min){
                    $error = "Minimum quantity is $min";
                    $error = urlencode($error);
                    header("location: ./user/cart.php?error=".$error);
                    exit();
                }


                $cost = $quantity * $price;
                $tmp = array('prod_id' => $prod_id, 'quantity' => $quantity, 'goods' => '', 'title' => $title);
                $purchaseArr[$prod_id] = $tmp;
                

                $total_cost = $total_cost + $cost;

                

            }

            $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
            $coupon = mysqli_real_escape_string($conn, $_POST['coupon']);
            $crypto_type = mysqli_real_escape_string($conn, $_POST['crypto_type']);


            $discount = 0;

            $sql = "SELECT * FROM `coups` WHERE `code` = '$coupon' AND `status` = 1";
            $result = mysqli_query($conn, $sql);
            if(mysqli_num_rows($result) != 0){
                $coupon = mysqli_fetch_assoc($result);
                $discount = $coupon['discount'];
            }

            $payments = array("Coinbase", "Sellix", "Cashapp","Poof");
            if(!in_array($payment_method, $payments)){
                $error = "Invalid payment method";
                $error = urlencode($error);
                header("location: ./user/cart.php?error=".$error);
                exit();
            }

            $sql = "SELECT * FROM `site_settings` WHERE `id`='1';";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);

            $coinbase_api = $row["coinbase_api"];
            $coinbase_secret = $row["coinbase_secret"];



            $total_cost = $total_cost - ($total_cost * ($discount / 100));

            $prod_id = json_encode($purchaseArr);

            if($payment_method == "Coinbase"){
                $post = array(
                    "name" => "Buy ".$items_count." items",
                    "description" => "Buy ".$items_count." items",
                    "local_price" => array(
                        'amount' => $total_cost,
                        'currency' => 'USD'
                    ),
                    "pricing_type" => "fixed_price"
                );
        
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.commerce.coinbase.com/charges');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
                
                $headers = array();
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'X-Cc-Api-Key: '.$coinbase_api;
                $headers[] = 'X-Cc-Version: 2018-03-22';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $response = curl_exec($ch);
                curl_close($ch);
            
            
             $bytes = openssl_random_pseudo_bytes(32);
                $get_order_id = bin2hex($bytes);
                
                $response = json_decode($response, true);
                $uqid = $response['data']['id'];
                $url = $response['data']['hosted_url'];
                $ip = $_SERVER['REMOTE_ADDR'];

                $sql = "INSERT INTO `orders` (`user_id`, `prod_ids`, `payment_method`, `status`, `amount`, `email`, `link`, `uqid`, `type`, `ip`, `get_order_id`) VALUES ('$id', '$prod_id', '$payment_method', '0', '$total_cost', '$email', '$url', '$uqid', 'buy', '$ip', '$get_order_id');";
                $result = mysqli_query($conn, $sql);
                

                $order_id = mysqli_insert_id($conn);
        
                header("location: order.php?id=".$order_id."&email=".$email);
                exit();

            }elseif ($payment_method == "Poof") {
                $poof_api_key = 'f-PM4WCQY6R6hHYoGz4YqA';
                $poof_url = 'https://www.poof.io/api/v2/create_invoice';
            
                $post = array(
                    "amount" => $total_cost,
                    "crypto" => $crypto_type
                );
            
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $poof_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
                
                $headers = array();
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Authorization: ' . $poof_api_key;
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                
                $response = curl_exec($ch);
                if ($response === false) {
                    curl_close($ch);
                    $error = "Poof API connection failed";
                    header("location: ./user/cart.php?error=" . urlencode($error));
                    exit();
                }
                curl_close($ch);
            
                $response = json_decode($response, true);
            
                if (isset($response['payment_link']) && isset($response['payment_id'])) {
                    $payment_link = $response['payment_link'];
                    $payment_id = $response['payment_id'];
            
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $bytes = openssl_random_pseudo_bytes(32);
                    $get_order_id = bin2hex($bytes);
            
                    $sql = "INSERT INTO `orders` (`user_id`, `prod_ids`, `payment_method`, `status`, `amount`, `email`, `link`, `uqid`, `type`, `ip`, `get_order_id`) VALUES ('$id', '$prod_id', '5', '4', '$total_cost', '$email', '$payment_link', '$payment_id', 'buy', '$ip', '$get_order_id');";
                    $result = mysqli_query($conn, $sql);
                    if (!$result) {
                        $error = "Database error: " . mysqli_error($conn);
                        header("location: ./user/cart.php?error=" . urlencode($error));
                        exit();
                    }
            
                    header("Location: " . $payment_link);
                    exit();
                } else {
                    $error = "Invalid response from Poof API";
                    header("location: ./user/cart.php?error=" . urlencode($error));
                    exit();
                }
            }else if($payment_method == "Cashapp"){
                
                $note = cashapp_note();
                $ip = $_SERVER['REMOTE_ADDR'];

                $sql = "INSERT INTO `orders` (`user_id`, `prod_ids`, `payment_method`, `status`, `amount`, `email`, `link`, `cashapp_note`, `type`, `ip`) VALUES ('$id', '$prod_id', '$payment_method', '1', '$total_cost', '$email', 'order', '$note', 'buy', '$ip');";
                $result = mysqli_query($conn, $sql);
        
                //get inserted id
                $order_id = mysqli_insert_id($conn);
        
                header("location: order.php?id=".$order_id."&email=".$email);
                exit();
            }






            
        }else{
            $error = "Cart is empty";
            $error = urlencode($error);
            header("location: ./user/cart.php?error=".$error);
            exit();
        }
    
    }

}else{
    $error = "Invalid request";
    $error = urlencode($error);
    header("location: ./user/cart.php?error=".$error);
    exit();
}


exit();
