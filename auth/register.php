<?php
session_start();

require_once __DIR__ . "/../server/db.php";
require_once __DIR__ . "/../comp/functions.php";

// Check if the user is already logged in, if yes then redirect to welcome page
checkLogin();

// Define variables and initialize with empty values
$username = $password = "";

// Ensure CSRF token is set for the session
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Functions for setting and displaying session messages
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
    if (isset($_POST['h-captcha-response']) && !empty($_POST['h-captcha-response'])) {
        $secret = '0x582b22252F6fF3592AED3f1215f9a72ebF49B895'; // Your secret key
        $verifyResponse = file_get_contents('https://hcaptcha.com/siteverify?secret='.$secret.'&response='.$_POST['h-captcha-response'].'&remoteip='.$_SERVER['REMOTE_ADDR']);
        $responseData = json_decode($verifyResponse);

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            setSessionMessage('danger', 'CSRF token validation failed.');
            header("Location: ./register.php");
            exit;
        }

        if ($responseData->success) {
            $username = mysqli_real_escape_string($conn, $_POST["username"]);
            $password = mysqli_real_escape_string($conn, $_POST["password"]);
            $email = mysqli_real_escape_string($conn, $_POST["email"]);
            $confirm_password = mysqli_real_escape_string($conn, $_POST["confirmpassword"]);
            $role = intval($_POST["role"]);
            $hash = md5(rand(0, 1000));


            validateEmail($email);
            validateUsername($username);
            validatePassword($password, $confirm_password);



            $email = trim($email);
            $username = trim($username);
            $password = trim($password);


            registerUser($username, $password, $email, $hash, $ip);
            
            setSessionMessage('success', 'You have successfully registered check your mail to verify.');
            header("Location: ./login.php");
            exit;
        } else {
            setSessionMessage('danger', 'Invalid captcha.');
            header("Location: ./register.php");
            exit;
        }
    } else {
        setSessionMessage('danger', 'Captcha verification required.');
        header("Location: ./register.php");
        exit;
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
    <?php include __DIR__ . "/../comp/header.php"; ?>
    <link rel="stylesheet" href="../assets/css/landing.css">
    <title>
        <?php echo $shop_title; ?> - Register
    </title>
    <script src="https://hcaptcha.com/1/api.js?hl=en" async="" defer=""></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>


    <style>
        .form-group {
            display: flex;
            align-items: center;
            padding: 5px;
        }

        .form-group img {
            margin-right: 10px;
            color: white;
            /* Adjust the margin as needed */
        }

        .toggle-password {
            /* Set the font size to 1px */
            color: white;
            margin-left: 10px;
            /* Set the color to white */
        }
    </style>
   
</head>

<body>
   


    <main class="main" id="top">

        <?php

include __DIR__ . "/../comp/nav.php";

        ?>
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
                                                <?php displaySessionMessage(); ?>



                                                <form method="POST">
                                                    <div class="text-center text-white">
                                                        <section class="faq_hero_section">
                                                            <div class="container">
                                                                <div class="row">
                                                                    <div class="col-lg-12">
                                                                        <div class="hero_txt">
                                                                            <h6>Registration</h6>
                                                                            <p>Get Access to New Payments!</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </section>
                                                    </div>

                                                    <div class="form-group">
                                                        <input type="hidden" name="csrf_token"
                                                            value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                        <input type="text" name="username" value="" required=""
                                                            style="margin-right: 29px;" autofocus=""
                                                            class="form-control" placeholder="Username">
                                                    </div>
                                                    <div class="form-group">
                                                        <input type="text" name="email" value="" required=""
                                                            style="margin-right: 29px;" autofocus=""
                                                            class="form-control" placeholder="Email">
                                                    </div>
                                                    <div class="form-group">
                                                        <input id="password-field" type="password" class="form-control"
                                                            name="password" placeholder="Password" required=""
                                                            style="width: calc(100% + 10px);"
                                                            style="margin-right: 3px;">
                                                        <span toggle="#password-field"
                                                            class="fa fa-fw fa-eye field-icon toggle-password"></span>
                                                    </div>

                                                    <div class="form-group">
                                                        <input id="confirmpassword-field" type="password"
                                                            class="form-control" name="confirmpassword"
                                                            placeholder="Confirm Password" required=""
                                                            style="width: calc(100% + 10px);">
                                                        <span toggle="#confirmpassword-field"
                                                            class="fa fa-fw fa-eye field-icon toggle-password"></span>
                                                    </div>

                                                    <div class="form-group d-flex justify-content-center">



                                                        <div class="h-captcha"
                                                            data-sitekey="c7011933-2d75-4c36-8f59-ae23b4da57e9"
                                                            data-theme="dark"></div>


                                                    </div>

                                                    <div
                                                        class="form-group mb-0 text-center d-flex justify-content-center align-items-center">
                                                        <button type="submit" class="btn btn_submit text-white"
                                                            style="background-color: rgba(25, 25, 235); color: white !important;">
                                                            Register
                                                        </button>
                                                    </div>






                                                </form>


                                                <p class="ppp">Already have an account? <a href="./login.php"
                                                        style="color: #006dc7; !important">Login</a></p>




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