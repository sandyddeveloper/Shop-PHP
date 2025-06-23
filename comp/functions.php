<?php


$sending_email = "no-reply@poland.fo";
$website_base = "https://poland.fo/shop";
$shop_name = "Poland";
$website_logo = "";



function randString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getUserData($id, $type){
    global $conn;
    $sql = "SELECT * FROM users WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);

    // Check if the query was successful
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Check if the array key exists before accessing it
        if ($user && isset($user[$type])) {
            return $user[$type];
        } else {
            // Handle the case where the key does not exist (return a default value, throw an error, etc.)
            return null; // Adjust this accordingly
        }
    } else {
        // Handle the case where the query was not successful
        return null; // Adjust this accordingly
    }
}


function getData($id, $type, $table){
    global $conn;
    $sql = "SELECT ".$type." FROM ".$table." WHERE id = '".$id."';";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);
    return $user[$type];
}

function generateRandomID($length = "5"){    
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';    
    $charactersLength = strlen($characters);    
    $randomString = '';    
    for ($i = 0; $i < $length; $i++) {        
        $randomString .= $characters[rand(0, $charactersLength - 1)];
        }    
    return $randomString;  
}

    
function cashapp_note($length = "5"){    
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';    
    $charactersLength = strlen($characters);    
    $randomString = '';    
    for ($i = 0; $i < $length; $i++) {        
        $randomString .= $characters[rand(0, $charactersLength - 1)];
        }    
    return $randomString;  
}


function isActive($page){
    $cur_page = basename($_SERVER['PHP_SELF']);
    if($cur_page == $page){
        return ' active';
    }else{
        return '';
    }
}


function time_elapsed_string($stringDate){
    $date = new DateTime($stringDate);
    $now = new DateTime();
    $diff = $now->diff($date);
    $w = 0;
    $diff->$w = floor($diff->d / 7);
    $diff->d -= $diff->$w * 7;
    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
    if (!$string) {
        return 'just now';
    }
    $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function checkLogin() {
    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        header("location: ../home.php");
        exit();
    }
}

function exitWithError($error) {
    //get current page url
    $currentPage = basename($_SERVER['PHP_SELF']);
    //redirect to same page with error message
    $error = urlencode($error);
    header("location: " . $currentPage . "?error=" . $error);
    exit();
}

function exitWithSuccess($success) {
    //get current page url
    $currentPage = basename($_SERVER['PHP_SELF']);
    //redirect to same page with error message
    $success = urlencode($success);
    header("location: " . $currentPage . "?success=" . $success);
    exit();
}

function login($username, $password){
    global $conn;

    $sql = "SELECT * FROM users WHERE username = '$username';";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row["password"])) {
            $id = $row["id"];
            $email = $row["email"];

            if ($row["verified"] == 1) {

                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $id;
                $_SESSION["username"] = ucfirst($username);
                $_SESSION["email"] = $email;

                return array(true, $id);

            }else{
                return array(false, "Account not verified");
            }

        } else {
            return array(false, "Incorrect username or password");
        }

    } else {
        return array(false, "Incorrect username or password");
    }

}

function validateUsername($username) {
    global $conn;
    if (empty(trim($username))) {
        exitWithError("Username is required.");
    } elseif (strlen(trim($username)) < 2) {
        exitWithError("Username must be at least 2 characters.");
    } elseif (strlen(trim($username)) > 20) {
        exitWithError("Username cannot be more than 20 characters.");
    } elseif (preg_match('/[^A-Za-z0-9]/', $username)) {
        exitWithError("Username must only contain letters and numbers.");
    } elseif (mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'")) > 0) {
        exitWithError("Username already exists.");
    } elseif (strlen(trim($username)) > 99) {
        exitWithError("Username cannot be more than 99 characters.");
    }elseif (preg_match('/[^A-Za-z0-9 ]/', $username)) {
        exitWithError("Username must only contain letters and numbers.");
    }

}


function validateEmail($email) {
    global $conn;
    if (empty(trim($email))) {
        exitWithError("Email is required.");
    } elseif (strlen(trim($email)) > 99) {
        exitWithError("Email cannot be more than 99 characters.");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        exitWithError("Email format is invalid.");
    }elseif (mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'")) > 0) {
        exitWithError("Email already exists.");
    }
}

function validateEmailWithReturn($email) {
    if (empty(trim($email))) {
        return false;
    } elseif (strlen(trim($email)) > 99) {
        return false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }else{
        return true;
    }

}

function validateEmailForReset($email) {
    global $conn;
    if (empty(trim($email))) {
        exitWithError("Email is required.");
    } elseif (strlen(trim($email)) > 99) {
        exitWithError("Email cannot be more than 99 characters.");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        exitWithError("Invalid email.");
    } elseif (mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'")) == 0) {
        exitWithError("Invalid email.");
    }
}

function validatePassword($password, $confirm_password) {
    global $conn;
    if (empty(trim($password))) {
        exitWithError("Password is required.");
    } elseif (strlen(trim($password)) < 8) {
        exitWithError("Password must be at least 8 characters.");
    } elseif (strlen(trim($password)) > 99) {
        exitWithError("Password cannot be more than 99 characters.");
    } elseif (!preg_match('/[0-9]/', $password)) {
        exitWithError("Password must contain at least one number.");
    } elseif (!preg_match('/[A-Za-z]/', $password)) {
        exitWithError("Password must contain at least one letter.");
    } elseif ($password != $confirm_password) {
        exitWithError("The two passwords do not match.");
    }
}
function registerUser($username, $password, $email, $hash, $ip) {
    global $conn;
    global $sending_email;
    global $website_base;
    global $shop_name;
    global $website_logo;
    $test = "no-reply@poland.fo";

    $password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, email, hash, ip) VALUES ('$username', '$password', '$email', '$hash', '$ip')";
    if (mysqli_query($conn, $sql)) {
        $to      = $email; // Send email to our user
       $subject = "Account Verification | Form $shop_name";
        // Construct the HTML email message
        $message = <<<HTML
<!doctype html>
<html lang="en-US">

<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    
    <style type="text/css">
        a:hover {text-decoration: underline !important;}
    </style>
</head>

<body marginheight="0" topmargin="0" marginwidth="0" style="margin: 0px; background-color: #f2f3f8;" leftmargin="0">
    <!-- 100% body table -->
    <table cellspacing="0" border="0" cellpadding="0" width="100%" bgcolor="#f2f3f8"
        style="@import url(https://fonts.googleapis.com/css?family=Rubik:300,400,500,700|Open+Sans:300,400,600,700); font-family: 'Open Sans', sans-serif;">
        <tr>
            <td>
                <table style="background-color: #f2f3f8; max-width:670px; margin:0 auto;" width="100%" border="0"
                    align="center" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="height:80px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="text-align:center;">
                            <a href="$website_base" title="logo" target="_blank">
                                <img width="200" height="120" src="$website_logo" title="logo" alt="logo">
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="height:20px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>
                            <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0"
                                style="max-width:670px; background:#fff; border-radius:3px; text-align:center;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);">
                                <tr>
                                    <td style="height:40px;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding:0 35px;">
                                        <h1 style="color:#1e1e2d; font-weight:500; margin:0;font-size:32px;font-family:'Rubik',sans-serif;">Get started
                                        </h1>
                                        <p style="font-size:15px; color:#455056; margin:8px 0 0; line-height:24px;">
                                            Your account has been created on the $shop_name <br><strong>Please Verify</strong>.</p>
                                        <span
                                            style="display:inline-block; vertical-align:middle; margin:29px 0 26px; border-bottom:1px solid #cecece; width:100px;"></span>
                                        <p
                                            style="color:#455056; font-size:18px;line-height:20px; margin:0; font-weight: 500;">
                                            <strong
                                                style="display: block;font-size: 13px; margin: 0 0 4px; color:rgba(0,0,0,.64); font-weight:normal;">Username</strong>$username
                                            
                                        </p>
                                        
                                     

                                        <a href="https://poland.fo/ver.php?email=$email&hash=$hash"
                                            style="background:#20e277;text-decoration:none !important; display:inline-block; font-weight:500; margin-top:24px; color:#fff;text-transform:uppercase; font-size:14px;padding:10px 24px;display:inline-block;border-radius:50px;">Click Here To Verify!</a>
                                  <br> <br>   
                                   <a href="https://poland.fo/ver.php?email=$email&hash=$hash" style="color: #20e277; text-decoration: underline;">Click Here To Verify!</a>
                                    
                                    </td>
                                </tr>
                                <tr>
                                    <td style="height:40px;">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="height:20px;">&nbsp;</td>
                    </tr>

                    <tr>
                        <td style="height:80px;">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!--/100% body table-->
</body>

</html>
HTML;

        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .= 'From: ' . $test . "\r\n";
        $headers .= 'CC: ' . $test . "\r\n"; // Set from headers
        if (!empty($email)) {
            mail($to, $subject, $message, $headers); // Send our email
        }
    }
}


function requestPasswordReset($email, $hashedTime, $hash) {
    global $conn;
    global $sending_email;
    global $website_base;
    global $shop_name;
    global $website_logo;

    $forget = "no-reply@poland.fo";

    $sql = "UPDATE `users` SET `hash`='$hashedTime' WHERE `email`='$email';";
    if (mysqli_query($conn, $sql)) {
        $to      = $email; // Send email to our user
        $subject = 'Password Reset '; // Give the email a subject 

        $message = <<<HTML
<!doctype html>
<html lang="en-US">

<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <style type="text/css">
        a:hover {text-decoration: underline !important;}
    </style>
</head>

<body marginheight="0" topmargin="0" marginwidth="0" style="margin: 0px; background-color: #f2f3f8;" leftmargin="0">
    <!--100% body table-->
    <table cellspacing="0" border="0" cellpadding="0" width="100%" bgcolor="#f2f3f8"
        style="@import url(https://fonts.googleapis.com/css?family=Rubik:300,400,500,700|Open+Sans:300,400,600,700); font-family: 'Open Sans', sans-serif;">
        <tr>
            <td>
                <table style="background-color: #f2f3f8; max-width:670px;  margin:0 auto;" width="100%" border="0"
                    align="center" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="height:80px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="text-align:center;">
                            <a href="$website_base" title="logo" target="_blank">
                                <img width="200" height="120" src="$website_logo" title="logo" alt="logo">
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="height:20px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>
                            <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0"
                                style="max-width:670px;background:#fff; border-radius:3px; text-align:center;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);">
                                <tr>
                                    <td style="height:40px;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding:0 35px;">
                                        <h1 style="color:#1e1e2d; font-weight:500; margin:0;font-size:32px;font-family:'Rubik',sans-serif;">You have
                                            requested to reset your password</h1>
                                        <span
                                            style="display:inline-block; vertical-align:middle; margin:29px 0 26px; border-bottom:1px solid #cecece; width:100px;"></span>
                                        <p style="color:#455056; font-size:15px;line-height:24px; margin:0;">
                                            We cannot simply send you your old password. A unique link to reset your
                                            password has been generated for you. To reset your password, click the
                                            following link and follow the instructions.
                                        </p>
                                        <a href="https://poland.fo/auth/resetp.php?email=$email&hash=$hash"
                                            style="background:#20e277;text-decoration:none !important; font-weight:500; margin-top:35px; color:#fff;text-transform:uppercase; font-size:14px;padding:10px 24px;display:inline-block;border-radius:50px;">Reset
                                            Password</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="height:40px;">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    <tr>
                        <td style="height:20px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="height:80px;">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!--/100% body table-->
</body>

</html>
HTML;

        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: ' . $forget . "\r\n";
        $headers .= 'CC: ' . $forget . "\r\n";
        if (!empty($email)) {
            mail($to, $subject, $message, $headers); // Send our email
        }
    }
}


function deliverOrder($order_id){
    global $conn;
    global $sending_email;
    global $shop_name;
    global $website_base;
    global $website_logo;
   
                
                
    
  

    mysqli_begin_transaction($conn);

    try {
        // Fetch the original order details
        $sql = "SELECT * FROM `orders` WHERE `id` = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $order_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $order = mysqli_fetch_assoc($result);
        
          $user_id = $order['user_id'];
    $prodArr = $order['prod_ids'];
    $email = $order['email'];
    $uuid = $order['uqid'];
    $amt  = $order['amount'];
    $payment = $order['payment_method'];
    $cashapp = $order['cashapp_note'];
    $cb = $order['link'];
    $date = $order['created_at'];
    $unique_id = $order['get_order_id'];
    $ip = $_SERVER['REMOTE_ADDR'];
    
        if (!$order) {
            throw new Exception("Order not found.");
        }

        $prodArr = json_decode($order['prod_ids'], true);
        if (!is_array($prodArr)) {
            throw new Exception("Invalid product data.");
        }

        foreach ($prodArr as $key => &$value) {
            $prod_id = $value['prod_id'];
            $quantity = $value['quantity'];

            // Fetch stock codes for the product
            $sql = "SELECT * FROM `stock` WHERE `prod_id` = ? LIMIT ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "si", $prod_id, $quantity);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) < $quantity) {
                throw new Exception("Not enough stock for product ID $prod_id.");
            }

            $codesToDelete = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $codesToDelete[] = $row['id'];
                $goods[] = $row['code']; // Assuming you're accumulating stock codes here
            }
            $value['goods'] = $goods; // Update the product array with allocated goods

            // Delete allocated stock
            if (!empty($codesToDelete)) {
                $idsStr = implode(',', $codesToDelete);
                $sql = "DELETE FROM `stock` WHERE `id` IN ($idsStr)";
                if (!mysqli_query($conn, $sql)) {
                    throw new Exception("Failed to delete stock for product ID $prod_id.");
                }
            }
        }
        unset($value); // Break the reference with the last element

        // Encode the updated product array back to JSON
        $prodArrJson = json_encode($prodArr);

        if (empty($order['get_order_id'])) {
            // Generate a new get_order_id if it doesn't exist
            $bytes = openssl_random_pseudo_bytes(32);
            $get_order_id = bin2hex($bytes);

            // Update the order with the new get_order_id
            $sqlUpdate = "UPDATE `orders` SET `get_order_id` = ? WHERE `id` = ?";
            $stmtUpdate = mysqli_prepare($conn, $sqlUpdate);
            mysqli_stmt_bind_param($stmtUpdate, "ss", $get_order_id, $order_id);
            mysqli_stmt_execute($stmtUpdate);
        } else {
            // Use the existing get_order_id
            $get_order_id = $order['get_order_id'];
        }
        // Update the original order with the new get_order_id, updated products, and status
        $sql = "UPDATE `orders` SET `status` = '2', `prod_ids` = ? WHERE `id` = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $prodArrJson, $order_id);
        mysqli_stmt_execute($stmt);

        
        mysqli_commit($conn);

        
    

 $delivery = "no-reply@poland.fo";
    $to      = $email;
    $subject = 'Order Delivered - #'.$order_id; 

    $headers = 'From:'.$delivery . "\r\n"; // Set from headers
    $headers .= 'CC: '.$delivery . "\r\n"; // Set from headers
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $message = <<<HTML
    <!doctype html>
    <html lang="en-US">

    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
        <title>Appointment Reminder Email Template</title>
        <meta name="description" content="Appointment Reminder Email Template">
    </head>
    <style>
        a:hover {text-decoration: underline !important;}
    </style>

    <body marginheight="0" topmargin="0" marginwidth="0" style="margin: 0px; background-color: #f2f3f8;" leftmargin="0">
        <table cellspacing="0" border="0" cellpadding="0" width="100%" bgcolor="#f2f3f8"
            style="@import url(https://fonts.googleapis.com/css?family=Rubik:300,400,500,700|Open+Sans:300,400,600,700); font-family: 'Open Sans', sans-serif;">
            <tr>
                <td>
                    <table style="background-color: #f2f3f8; max-width:670px; margin:0 auto;" width="100%" border="0"
                        align="center" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="height:80px;">&nbsp;</td>
                        </tr>
                        <!-- Logo -->
                         <tr>
                            <td style="text-align:center;">
                                <a href="$website_base" title="logo" target="_blank">
                                    <img width="200" height="120" src="$website_logo" title="logo" alt="logo">
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="height:20px;">&nbsp;</td>
                        </tr>
                        <!-- Email Content -->
                        <tr>
                            <td>
                                <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0"
                                    style="max-width:670px; background:#fff; border-radius:3px;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);padding:0 40px;">
                                    <tr>
                                        <td style="height:40px;">&nbsp;</td>
                                    </tr>
                                    <!-- Title -->
                                    <tr>
                                        <td style="padding:0 15px; text-align:center;">
                                            <h1 style="color:#1e1e2d; font-weight:400; margin:0;font-size:32px;font-family:'Rubik',sans-serif;"><strong> Here Is Your Delivery!</strong></h1>
                                            <span style="display:inline-block; vertical-align:middle; margin:29px 0 26px; border-bottom:1px solid #cecece; 
                                            width:100px;"></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <table cellpadding="0" cellspacing="0"
                                                style="width: 100%; border: 1px solid #ededed">
                                                <tbody>
                                                    <tr>
                                                        <td
                                                            style="padding: 10px; border-bottom: 1px solid #ededed; border-right: 1px solid #ededed; width: 35%; font-weight:500; color:rgba(0,0,0,.64)">
                                                            Order ID :</td>
                                                        <td
                                                            style="padding: 10px; border-bottom: 1px solid #ededed; color: #455056;">
                                                            $order_id</td>
                                                    </tr>
                                                    <tr>
                                                        <td
                                                            style="padding: 10px; border-bottom: 1px solid #ededed; border-right: 1px solid #ededed; width: 35%; font-weight:500; color:rgba(0,0,0,.64)">
                                                            Amount :</td>
                                                        <td
                                                            style="padding: 10px; border-bottom: 1px solid #ededed; color: #455056;">
                                                            $amt</td>
                                                    </tr>
                                                    <tr>
                                                        <td
                                                            style="padding: 10px; border-bottom: 1px solid #ededed; border-right: 1px solid #ededed; width: 35%; font-weight:500; color:rgba(0,0,0,.64)">
                                                            Payment Method :</td>
                                                        <td
                                                            style="padding: 10px; border-bottom: 1px solid #ededed; color: #455056;">
                                                            $payment</td>
                                                    </tr>
                                                    <tr>
                                                        <td
                                                            style="padding: 10px; border-bottom: 1px solid #ededed;border-right: 1px solid #ededed; width: 35%; font-weight:500; color:rgba(0,0,0,.64)">
                                                            Cash app : </td>
                                                        <td
                                                            style="padding: 10px; border-bottom: 1px solid #ededed; color: #455056;">
                                                            $cashapp</td>
                                                    </tr>
                                                    <tr>
                                                        <td
                                                            style="padding: 10px;  border-bottom: 1px solid #ededed; border-right: 1px solid #ededed; width: 35%; font-weight:500; color:rgba(0,0,0,.64)">
                                                            Orderd url : </td>
                                                        <td
                                                            style="padding: 10px; border-bottom: 1px solid #ededed; color: #455056;">
                                                           $cb</td>
                                                    </tr>
                                                    <tr>
                                                        <td
                                                            style="padding: 10px; border-bottom: 1px solid #ededed; border-right: 1px solid #ededed; width: 35%;font-weight:500; color:rgba(0,0,0,.64)">
                                                            Bought on :</td>
                                                        <td
                                                            style="padding: 10px; border-bottom: 1px solid #ededed; color: #455056; ">
                                                            $date</td>
                                                    </tr>
                                                  
                                                    <tr>
                                                        <td
                                                            style="padding: 10px; border-right: 1px solid #ededed; width: 35%;font-weight:500; color:rgba(0,0,0,.64)">
                                                            Get your order:</td>
                                                        <td
                                                           <td style="padding: 10px; border-bottom: 1px solid #ededed; color: #455056; ">
            <a href="https://poland.fo/view_order.php?id={$unique_id}">https://poland.fo/view_order.php?id={$unique_id}</a></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="height:40px;">&nbsp;</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="height:20px;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="text-align:center;">
                                    <p style="font-size:14px; color:#455056bd; line-height:18px; margin:0 0 0;">&copy; <strong>$shop_name</strong></p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>

    </html>
HTML;

 if (!empty($email) && !mail($to, $subject, $message, $headers)) {
    throw new Exception("Failed to send email.");
}



    // Create an array for the data to be sent to the Discord webhook
    $discordData = array(
        'embeds' => array(
            array(
                'title' => 'Order Delivered - #' . $order_id,
            'description' => "Order ID: $order_id\nAmount: $amt\nPayment Method: $payment\nDate: $date \n Email: $email \n uuid: $uuid \n user_id: $user_id \n",
                'color' => hexdec('00FF00'), // Green color
                'thumbnail' => array(
                    'url' => 'http://localhost/shops/images/bg.png', // Replace with the URL of your image
                ),
                'fields' => array(
                    array(
                        'name' => 'Products',
                        'value' => "```json\n$prodArr\n```", // Display JSON data in a code block
                    ),
                    array(
                        'name' => 'Goods',
                        'value' => "```\n$goodsString\n```", // Display goods in a code block
                    ),
                    array(
                        'name' => 'View Order',
                        'value' => "[Click Here]($website_base/order.php?id=$order_id)", // Create a clickable link
                    ),
                    array(
                        'name' => 'Replace',
                        'value' => "[Click Here]($website_base/order.php?id=$order_id)", // Create a clickable link
                    ),
                    array(
                        'name' => 'Refund as balance',
                        'value' => "[Click Here]($website_base/order.php?id=$order_id&act=refund_balance)", // Create a clickable link
                    ),
                ),
            ),
        ),
    );
    
    

    // Encode the data as JSON
    $jsonPayload = json_encode($discordData);


    $sql = "SELECT * FROM `site_settings` WHERE `id` = 1";
    $result = mysqli_query($conn, $sql);
    $set = mysqli_fetch_assoc($result);

    $webhook = $set['discord_webhook'];

    // Create cURL request to send data to Discord webhook
    $ch = curl_init($webhook);
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        return array("code" => false, "error" => "cURL error: " . curl_error($ch));
    }

    // Check if the Discord webhook request was successful
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode !== 204) {
        return array("code" => false, "error" => "Discord webhook error: HTTP code $httpCode");
    }

    // Close cURL session
    curl_close($ch);
    return array("code" => true);
} catch (Exception $e) {
        mysqli_rollback($conn);
        return array("code" => false, "error" => $e->getMessage());
    }
}



function replaceOrder($order_id, $prod_id, $amount){
    global $conn;
    global $sending_email;
     global $shop_name;
    global $website_base;
    global $website_logo;
    
    $replacements_email = "no-reply@poland.fo";
    $sql = "SELECT * FROM `orders` WHERE `id` = '$order_id'";
    $result = mysqli_query($conn, $sql);
    $order = mysqli_fetch_assoc($result);

    $user_id = $order['user_id'];
    $prodArr = $order['prod_ids'];
    $email = $order['email'];
    $replace = $order['replace_order'];
    $amt  = $order['amount'];
    $payment = $order['payment_method'];
    $cashapp = $order['cashapp_note'];
    $cb = $order['link'];
    $date = $order['created_at'];


    //this is a function to replace an item in an order by amount

    $prodArr = json_decode($prodArr, true);

    if(!empty($replace)){
        $replace = json_decode($replace, true);
    }else{
        $replace = array();
    }

    //check if prod_id is in order
    $found = false;

    foreach($prodArr as $key => $value){
        if($value['prod_id'] == $prod_id){
            $found = true;
            break;
        }
    }

    if(!$found){
        return array("code" => false, "error" => "product not found in order.");
    }

    //check if prod_id is a real product
    $sql = "SELECT * FROM `prods` WHERE `id` = '$prod_id'";
    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result) == 0){
        return array("code" => false, "error" => "product not found.");
    }
    $prod_title = mysqli_fetch_assoc($result)['title'];
    

    //check if product stock is enough
    $sql = "SELECT * FROM `stock` WHERE `prod_id` = '$prod_id'";
    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result) < $amount){
        return array("code" => false, "error" => "not enough stock.");
    }

    
    //get x amount of codes from stock for prod_id
    $sql = "SELECT * FROM `stock` WHERE `prod_id` = '$prod_id' LIMIT $amount";
    $result = mysqli_query($conn, $sql);

    $goods = array();
    $goodsString = "";
    $sql = "DELETE FROM `stock` WHERE";
    while($row = mysqli_fetch_assoc($result)){
        $code = $row['code'];
        $code_id = $row['id'];

        //delete stock
        $sql .= " `id` = '$code_id' OR";
        $goods[] = $code;
        $goodsString .= $code."\n";
    }

    $sql = substr($sql, 0, -3);
    $sql .= ";";
    mysqli_query($conn, $sql);


    //push to replace array using array push
    array_push($replace, array($prod_id => array("amount" => $amount, "goods" => $goods, "prod_id" => $prod_id, "title" => $prod_title, "quantity" => $amount)));


    //update order
    $replace = json_encode($replace);



    $sql = "UPDATE `orders` SET `replace_order` = '$replace' WHERE `id` = '$order_id'";
    mysqli_query($conn, $sql);
    $to      = $email;
    $subject = 'Order Replaced - #'.$order_id; 
    $headers = 'From:'.$replacements_email . "\r\n"; // Set from headers
    $headers .= 'CC: '.$replacements_email . "\r\n"; // Set from headers
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $message = <<<HTML

<!doctype html>
<html lang="en-US">

<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
</head>
<style>
    a:hover {text-decoration: underline !important;}
</style>

<body marginheight="0" topmargin="0" marginwidth="0" style="margin: 0px; background-color: #f2f3f8;" leftmargin="0">
    <table cellspacing="0" border="0" cellpadding="0" width="100%" bgcolor="#f2f3f8"
        style="@import url(https://fonts.googleapis.com/css?family=Rubik:300,400,500,700|Open+Sans:300,400,600,700); font-family: 'Open Sans', sans-serif;">
        <tr>
            <td>
                <table style="background-color: #f2f3f8; max-width:670px; margin:0 auto;" width="100%" border="0"
                    align="center" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="height:80px;">&nbsp;</td>
                    </tr>
                    <!-- Logo -->
                     <tr>
                        <td style="text-align:center;">
                            <a href="$website_base" title="logo" target="_blank">
                                <img width="200" height="120" src="$website_logo" title="logo" alt="logo">
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="height:20px;">&nbsp;</td>
                    </tr>
                    <!-- Email Content -->
                    <tr>
                        <td>
                            <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0"
                                style="max-width:670px; background:#fff; border-radius:3px;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);padding:0 40px;">
                                <tr>
                                    <td style="height:40px;">&nbsp;</td>
                                </tr>
                                <!-- Title -->
                                <tr>
                                    <td style="padding:0 15px; text-align:center;">
                                        <h1 style="color:#1e1e2d; font-weight:400; margin:0;font-size:32px;font-family:'Rubik',sans-serif;"><strong> Here Is Your Delivery!</strong></h1>
                                        <span style="display:inline-block; vertical-align:middle; margin:29px 0 26px; border-bottom:1px solid #cecece; 
                                        width:100px;"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table cellpadding="0" cellspacing="0"
                                            style="width: 100%; border: 1px solid #ededed">
                                            <tbody>
                                                <tr>
                                                    <td
                                                        style="padding: 10px; border-bottom: 1px solid #ededed; border-right: 1px solid #ededed; width: 35%; font-weight:500; color:rgba(0,0,0,.64)">
                                                        Order ID :</td>
                                                    <td
                                                        style="padding: 10px; border-bottom: 1px solid #ededed; color: #455056;">
                                                        $order_id</td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="padding: 10px; border-bottom: 1px solid #ededed; border-right: 1px solid #ededed; width: 35%; font-weight:500; color:rgba(0,0,0,.64)">
                                                        Amount :</td>
                                                    <td
                                                        style="padding: 10px; border-bottom: 1px solid #ededed; color: #455056;">
                                                        $amt</td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="padding: 10px; border-bottom: 1px solid #ededed; border-right: 1px solid #ededed; width: 35%; font-weight:500; color:rgba(0,0,0,.64)">
                                                        Payment Method :</td>
                                                    <td
                                                        style="padding: 10px; border-bottom: 1px solid #ededed; color: #455056;">
                                                        $payment</td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="padding: 10px; border-bottom: 1px solid #ededed;border-right: 1px solid #ededed; width: 35%; font-weight:500; color:rgba(0,0,0,.64)">
                                                        Cash app : </td>
                                                    <td
                                                        style="padding: 10px; border-bottom: 1px solid #ededed; color: #455056;">
                                                        $cashapp</td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="padding: 10px;  border-bottom: 1px solid #ededed; border-right: 1px solid #ededed; width: 35%;font-weight:500; color:rgba(0,0,0,.64)">
                                                        Orderd url : </td>
                                                    <td
                                                        style="padding: 10px; border-bottom: 1px solid #ededed; color: #455056;">
                                                       $cb</td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="padding: 10px; border-bottom: 1px solid #ededed; border-right: 1px solid #ededed; width: 35%;font-weight:500; color:rgba(0,0,0,.64)">
                                                        Bought on :</td>
                                                    <td
                                                        style="padding: 10px; border-bottom: 1px solid #ededed; color: #455056; ">
                                                        $date</td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="padding: 10px; border-right: 1px solid #ededed; width: 35%;font-weight:500; color:rgba(0,0,0,.64)">
                                                        Good :</td>
                                                    <textarea style="width: 355px; height: 75px;" readonly>$goodsString</textarea>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="height:40px;">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="height:20px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="text-align:center;">
                                <p style="font-size:14px; color:#455056bd; line-height:18px; margin:0 0 0;">&copy; <strong>$shop_name</strong></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
HTML;



    if(!empty($email)){
        mail($to, $subject, $message, $headers); // Send our email
    }


    return array("code" => true);
    
}

?>