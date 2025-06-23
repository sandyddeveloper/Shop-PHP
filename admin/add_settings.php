<?php
session_start();
require_once(__DIR__ . '/../server/db.php');
require_once(__DIR__ . '/../comp/functions.php');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$id = $_SESSION["id"];
$username = $_SESSION["username"];
$role = intval(getUserData($id, "role"));

if ($role != 1) {
    header("location: ../user/dashboard.php");
    exit;
}

$sql = "SELECT * FROM `site_settings` WHERE `id`='1';";
$result = mysqli_query($conn, $sql);
$settings = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $coinbase_api = mysqli_real_escape_string($conn, $_POST['coinbase_api']);
    $coinbase_secret = mysqli_real_escape_string($conn, $_POST['coinbase_secret']);
    $cashapp_cashtag = mysqli_real_escape_string($conn, $_POST['cashapp_cashtag']);
    $home = mysqli_real_escape_string($conn, $_POST['homepage_url']);
    $logo = mysqli_real_escape_string($conn, $_POST['logo']);
    $theme = intval($_POST['theme']);
    $discord = mysqli_real_escape_string($conn, $_POST['discord']);
    $telegram = mysqli_real_escape_string($conn, $_POST['telegram']);
    $google_analytics = mysqli_real_escape_string($conn, $_POST['google_analytics']);
    $crisp_key = mysqli_real_escape_string($conn, $_POST['crisp_key']);
    $announcement = mysqli_real_escape_string($conn, $_POST['home_announcement']);
    $announcement_url = mysqli_real_escape_string($conn, $_POST['home_announcement_link']);
    $balance = isset($_POST['balance_integration']) ? 1 : 0;
    $coinbase = isset($_POST['coinbase_integration']) ? 1 : 0;
    $hoodpay = isset($_POST['hoodpay_integration']) ? 1 : 0;
    $cashapp = isset($_POST['cashapp_integration']) ? 1 : 0;
    $sn = mysqli_real_escape_string($conn, $_POST['shop_name']);
    $dia = mysqli_real_escape_string($conn, $_POST['home_dia']);
    $discord_webhook = mysqli_real_escape_string($conn, $_POST['discord_webhook']);
    $background_url = mysqli_real_escape_string($conn, $_POST['background_url']);
    $credit_after_feedback = intval($_POST['feedback_amount']);
    $feedback_credits = intval($_POST['feedback_credits']);
    $automatic_feedback = intval($_POST['automatic_feedback']);

    $sql = "UPDATE `site_settings` SET 
            `coinbase_api`=?, `coinbase_secret`=?, `cashapp_cashtag`=?, `theme`=?, 
            `discord`=?, `telegram`=?, `homepage_url`=?, `logo`=?, `google`=?, 
            `crisp`=?, `home_announcement`=?, `home_announcement_link`=?, 
            `hoodpay`=?, `coinbase`=?, `balance`=?, `cashapp`=?, 
            `shop_name`=?, `home_dia`=?, `discord_webhook`=?, `background_url`=?, 
            `credit_after_feedback`=?, `feedback_credits`=?, `automatic_feedback`=? 
            WHERE `id`='1'";

    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param(
            $stmt,
            "sssiisssssssiiisssssiii",
            $coinbase_api,
            $coinbase_secret,
            $cashapp_cashtag,
            $theme,
            $discord,
            $telegram,
            $home,
            $logo,
            $google_analytics,
            $crisp_key,
            $announcement,
            $announcement_url,
            $hoodpay,
            $coinbase,
            $balance,
            $cashapp,
            $sn,
            $dia,
            $discord_webhook,
            $background_url,
            $credit_after_feedback,
            $feedback_credits,
            $automatic_feedback
        );

        if (mysqli_stmt_execute($stmt)) {
            header("location: add_settings.php?success=" . urlencode("Settings updated successfully!"));
            exit;
        } else {
            header("location: add_settings.php?error=" . urlencode("Something went wrong, please try again later."));
            exit();
        }
       $stmt->close();
    } else {
        header("location: add_settings.php?error=" . urlencode("Unable to prepare the database query."));
        exit();
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
        <?php echo $shop_title; ?> - Settings Manager
    </title>
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
                                    <h5>Settings Manager</h5>
                                    <p>Manage your site settings here.</p>
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



                        <div class="row mt-2 mb-5">

                            <form method="post" class="col-lg-12">

                                <div class="mb-3">
                                    <label class="form-label text-white">Shop Name : </label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark" name="shop_name"
                                        placeholder="shop Name" value="<?php echo $settings['shop_name']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-white">Home page slogan: </label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark" name="home_dia"
                                        placeholder="Home page slogan" value="<?php echo $settings['home_dia']; ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-white">Coinbase API Key</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark" name="coinbase_api"
                                        placeholder="Coinbase API Key" value="<?php echo $settings['coinbase_api']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-white">Coinbase API Secret</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark"
                                        name="coinbase_secret" placeholder="Coinbase API Secret"
                                        value="<?php echo $settings['coinbase_secret']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-white">Cashapp Cashtag</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark"
                                        name="cashapp_cashtag" placeholder="Cashapp Cashtag"
                                        value="<?php echo $settings['cashapp_cashtag']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-white">Home announcement Msg</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark"
                                        name="home_announcement" placeholder="Home page announcement"
                                        value="<?php echo $settings['home_announcement']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-white">Home announcement Url</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark"
                                        name="home_announcement_link" placeholder="Home page announcement url"
                                        value="<?php echo $settings['home_announcement_link']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-white">Home Page Admin url</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark" name="homepage_url"
                                        placeholder="Home Page Admin url"
                                        value="<?php echo $settings['homepage_url']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-white">Home Page logo</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark" name="logo"
                                        placeholder="Home Page logo" value="<?php echo $settings['logo']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-white">Google Analytics Key</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark"
                                        name="google_analytics" placeholder="Google Analytics Key"
                                        value="<?php echo $settings['google']; ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-white">Crisp Key</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark" name="crisp_key"
                                        placeholder="Crisp Key" value="<?php echo $settings['crisp']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-white">Discord webhook</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark"
                                        name="discord_webhook" placeholder="discord webhook"
                                        value="<?php echo $settings['discord_webhook']; ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-white">Site Theme </label>
                                    <select class="form-control mb-3 text-white bg-dark" name="theme"
                                        aria-label="Default select example">
                                        <option value="1" <?php if ($settings['theme'] == "1") {
                                            echo "selected";
                                        } ?>>
                                            Default</option>
                                        <option value="2" <?php if ($settings['theme'] == "2") {
                                            echo "selected";
                                        } ?>>
                                            Haloween</option>
                                        <option value="3" <?php if ($settings['theme'] == "3") {
                                            echo "selected";
                                        } ?>>Night
                                            Light</option>
                                        <option value="4" <?php if ($settings['theme'] == "4") {
                                            echo "selected";
                                        } ?>>
                                            Colored Glow</option>
                                        <option value="5" <?php if ($settings['theme'] == "5") {
                                            echo "selected";
                                        } ?>>
                                            Background Image</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-white">Background Image</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark"
                                        name="background_url" placeholder="background_url"
                                        value="<?php echo $settings['background_url']; ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-white">Discord (Empty to disable)</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark" name="discord"
                                        placeholder="Discord" value="<?php echo $settings['discord']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-white">Telegram (Empty to disable)</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark" name="telegram"
                                        placeholder="telegram" value="<?php echo $settings['telegram']; ?>">
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label text-white">Balance Payment</label>
                                        <div class="custom-control custom-switch">
                                            <input class="custom-control-input" type="checkbox" id="balanceSwitch"
                                                name="balance_integration" <?php echo ($settings['balance'] == 1) ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="balanceSwitch"></label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label text-white">Coinbase Payment</label>
                                        <div class="custom-control custom-switch">
                                            <input class="custom-control-input" type="checkbox" id="coinbaseSwitch"
                                                name="coinbase_integration" <?php echo ($settings['coinbase'] == 1) ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="coinbaseSwitch"></label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label text-white">Hoodpay Payment</label>
                                        <div class="custom-control custom-switch">
                                            <input class="custom-control-input" type="checkbox" id="hoodpaySwitch"
                                                name="hoodpay_integration" <?php echo ($settings['hoodpay'] == 1) ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="hoodpaySwitch"></label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label text-white">Cashapp Payment</label>
                                        <div class="custom-control custom-switch">
                                            <input class="custom-control-input" type="checkbox" id="cashappSwitch"
                                                name="cashapp_integration" <?php echo ($settings['cashapp'] == 1) ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="cashappSwitch"></label>
                                        </div>
                                    </div>
                                </div>


                                <div class="mb-3">
                                    <label class="form-label text-white">feedback_credits Status</label>
                                    <select class="form-control mb-3 text-white bg-dark" name="feedback_credits"
                                        aria-label="Default select example">
                                        <option value="0" <?php if ($settings['feedback_credits'] == "0") {
                                            echo "selected";
                                        } ?>>
                                            Disabled</option>
                                        <option value="1" <?php if ($settings['feedback_credits'] == "1") {
                                            echo "selected";
                                        } ?>>
                                            Enabled</option>
                                    </select>
                                </div>




                                <div class="mb-3">
                                    <label class="form-label text-white">Balance for Feedback</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark" name="feedback_amount"
                                        placeholder="feedback_amount" value="<?php echo $settings['credit_after_feedback']; ?>">
                                </div>

                                
                                <div class="mb-3">
                                    <label class="form-label text-white">Automattic Feedback</label>
                                    <select class="form-control mb-3 text-white bg-dark" name="automatic_feedback"
                                        aria-label="Default select example">
                                        <option value="0" <?php if ($settings['automatic_feedback'] == "0") {
                                            echo "selected";
                                        } ?>>
                                            Disabled</option>
                                        <option value="1" <?php if ($settings['automatic_feedback'] == "1") {
                                            echo "selected";
                                        } ?>>
                                            Enabled</option>
                                    </select>
                                </div>



                                <button type="submit" class="btn btn-danger w-100">Submit</button>
                            </form>


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

        search.oninput = function () {
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