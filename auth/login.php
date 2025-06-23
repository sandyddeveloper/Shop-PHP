<?php

session_start();

require_once __DIR__ . "/../server/db.php";
require_once __DIR__ . "/../comp/functions.php";

checkLogin();
$username = $password = "";

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function setSessionMessage($type, $message) {
    $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
}

function displaySessionMessage() {
    if (isset($_SESSION['flash_message'])) {
        $msgData = $_SESSION['flash_message'];
        echo "<div class=\"alert alert-{$msgData['type']}\" role=\"alert\">";
        echo htmlspecialchars($msgData['message']);
        echo '</div>';
        unset($_SESSION['flash_message']);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check for hCaptcha response
    if(isset($_POST['h-captcha-response']) && !empty($_POST['h-captcha-response'])){
        // Your secret key
        $secret = '0x582b22252F6fF3592AED3f1215f9a72ebF49B895';
        $verifyResponse = file_get_contents('https://hcaptcha.com/siteverify?secret='.$secret.'&response='.$_POST['h-captcha-response'].'&remoteip='.$_SERVER['REMOTE_ADDR']);
        $responseData = json_decode($verifyResponse);

        // CSRF token check
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            setSessionMessage('danger', 'CSRF token validation failed.');
            header("Location: ./login.php");
            exit;
        }

        // If hCaptcha verification is successful
        if($responseData->success){
            $user = mysqli_real_escape_string ($conn, $_POST["username"]);
            $pwd = mysqli_real_escape_string ($conn, $_POST["password"]);

            // Validation for empty fields
            if (empty(trim($user)) || empty(trim($pwd))) {
                setSessionMessage('danger', 'Username and Password are required.');
                header("Location: ./login.php");
                exit;
            }

            $username = trim($user);
            $password = trim($pwd);

            // Attempt to log in
            $login = login($username, $password);
            if ($login[0] == true) {
                header("Location: ../user/myorders.php");
                exit();
            } else {
                setSessionMessage('danger', $login[1]);
                header("Location: ./login.php");
                exit;
            }
        }else{
            setSessionMessage('danger', 'Invalid captcha.');
            header("Location: ./login.php");
            exit;
        }
    }else{
        setSessionMessage('danger', 'Captcha verification required.');
        header("Location: ./login.php");
        exit;
    }
}

?>



<!DOCTYPE html>
<html lang="en-US">
<head>
    <?php include __DIR__ . "/../comp/header.php"; ?>
    <link rel="stylesheet" href="../assets/css/landing.css">
    <title><?php echo $shop_title; ?> - Login</title>
    <script src="https://hcaptcha.com/1/api.js?hl=en" async="" defer=""></script>
    <style>
      .btn-purple {
        background-color: rgba(25, 25, 235);
        color: white;
      }
      .pur 
      {
        color:  #006dc7;
      }
    </style>

</head>
<body>

    <main class="main" id="top">
        <?php include __DIR__ . "/../comp/nav.php"; ?>
        <div class="dashboard_main_sec">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <section class="signup_section">
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-lg-6">
                                        <div class="signup_inner_wrap">
                                            <div class="signup_frm_wrap">
                                                <?php
                                             displaySessionMessage();
                                                ?>
                                                
                                              

                                             <div class="text-center text-white">
    <section class="faq_hero_section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="hero_txt">
                            <h6>Login</h6>
                            <p>Welcome Back!.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
</div> 

                                                <form method="POST">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                    <div class="form-group">
                                                        <label for="email" class="block mb-2 text-sm font-medium text-white bold dark:text-white">Username</label>
                                                        <input type="text" name="username" value="" required="" autofocus="" class="form-control" placeholder="Username">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="password" class="block mb-2 text-sm font-medium text-white dark:text-white">Password</label>
                                                        <input id="password-field" type="password" class="form-control" name="password" placeholder="Password" required="">
                                                    </div>
                                                    <div class="form-group d-flex justify-content-center">
                                                        <div class="h-captcha" data-sitekey="c7011933-2d75-4c36-8f59-ae23b4da57e9" data-theme="dark"></div>
                                                    </div>
                                                    <div class="form-group mb-0">
                                                    <div class="flex justify-center">
                                                    <div class="text-center">
                                                    <button type="submit" class="btn btn_submit btn-purple">Log On</button>
</div>


<div class="text-center"> <br><br>
                                                        <a href="./reset.php" class="frgt_pass text-white">Forgot Password?</a>
</div>
                                                    </div>
                                                </form>
                                                <p class="ppp">Don't have an account? <a href="./register.php" style="color: #006dc7; !important">Create one now!</a></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>

        
    </main>
    <?php include __DIR__ . "/../comp/footer.php"; ?>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
