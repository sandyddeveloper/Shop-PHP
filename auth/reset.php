<?php

session_start();
require_once __DIR__ . "/../server/db.php";
require_once __DIR__ . "/../comp/functions.php";


function displaySessionMessage() {
    if (isset($_SESSION['flash_message'])) {
        $msgData = $_SESSION['flash_message'];
        echo "<div class=\"alert alert-{$msgData['type']}\" role=\"alert\">";
        echo htmlspecialchars($msgData['message']);
        echo '</div>';
        unset($_SESSION['flash_message']); // Clear the message after displaying it
    }
}

// Check if the user is already logged in, if yes then redirect him to welcome page
checkLogin();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['h-captcha-response']) && !empty($_POST['h-captcha-response'])) {
        // Your secret key
        $secret = '0x59Fb3D59DA04b6AF996866e611a4cf5993cefAd3';
        $verifyResponse = file_get_contents('https://hcaptcha.com/siteverify?secret=' . $secret . '&response=' . $_POST['h-captcha-response'] . '&remoteip=' . $_SERVER['REMOTE_ADDR']);
        $responseData = json_decode($verifyResponse);

        if ($responseData->success) {
            $email = mysqli_real_escape_string($conn, $_POST["email"]);
            validateEmailForReset($email);

            requestPasswordReset($email, $hashedTime, $hash);
            
            if (validateEmailForReset($email) && requestPasswordReset($email, $hashedTime, $hash)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => "Please check your email for a link to reset your password."];
            } else {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => "Please check your email for a link to reset your password."];
            }
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => "Invalid captcha."];
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => "Captcha verification required."];
    }
    header("location: reset.php");
    exit;
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
        <?php echo $shop_title; ?> - Reset Password
    </title>
    <script src="https://hcaptcha.com/1/api.js?hl=en" async="" defer=""></script>
</head>
<style>
    .btn-purple {
        background-color: rgba(0, 119, 255, 0.9);
        color: white;
    }
</style>

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

                                                <section class="faq_hero_section">
                                                    <div class="container">
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <div class="hero_txt">
                                                                    <h6>Reset Password</h6>
                                                                    <p>Forgot your password? we got you <3!< /p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </section>
                                                <br><br>


                                                <form method="POST">


                                                    <div class="form-group">
                                                        <input type="text" name="email" value="" required=""
                                                            autofocus="" class="form-control" placeholder="Email">
                                                    </div>

                                                    <div class="form-group d-flex justify-content-center">



                                                        <div class="h-captcha"
                                                            data-sitekey="1ea3ac6b-f8d4-4cb6-9e66-98dcfda3d292"
                                                            data-theme="dark"></div>


                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <div class="flex justify-center">
                                                            <div class="text-center">
                                                                <button type="submit"
                                                                    class="btn btn_submit btn-purple">Reset</button>
                                                                <div class="text-center"
                                                                    style="margin-top: 20px; margin-bottom: 15px">
                                                                    <br><br>

                                                                    <a href="login.php"
                                                                        class="frgt_pass text-white">Back to login</a>
                                                                </div>
                                                            </div>

                                                </form>



                                                <p class="ppp">Don't have an account? <a href="./register.php">Create
                                                        one
                                                        now!</a></p>




                                            </div>
                                        </div>
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

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/script.js"></script>






</body>

</html>