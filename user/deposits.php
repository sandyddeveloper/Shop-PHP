<?php


session_start();
error_reporting(E_ALL);

require_once __DIR__ . "/../vendor/autoload.php";

require_once __DIR__ . "/../server/db.php";
require_once __DIR__ . "/../comp/functions.php";


if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit();
}

$id = $_SESSION["id"];
$username = $_SESSION["username"];
$role = intval(getUserData($id, "role"));
$bal = getUserData($id, "bal");

// Get site settings
$sql = "SELECT * FROM `site_settings` WHERE `id`='1';";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

$coinbase_api = $row["coinbase_api"];
$coinbase_secret = $row["coinbase_secret"];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_methods = array("Cashapp", "Coinbase", "Sellix");
    $method = mysqli_real_escape_string($conn, $_POST['method']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);

    if (empty($method) || empty($amount)) {
        $error = "Please fill all fields";
        $error = urlencode($error);
        header("location: deposits.php?error=" . $error);
        exit();
    }

    if (!in_array($method, $payment_methods)) {
        $error = "Invalid payment method";
        $error = urlencode($error);
        header("location: deposits.php?error=" . $error);
        exit();
    }

    $amount = intval($amount);

    if ($amount < 0) {
        $error = "Minimum deposit is 0";
        $error = urlencode($error);
        header("location: deposits.php?error=" . $error);
        exit();
    }

    if ($method == "Coinbase") {
        $post = array(
            "name" => "Balance Deposit",
            "description" => "Deposit " . $amount . " USD to " . ucfirst($username),
            "local_price" => array(
                'amount' => $amount,
                'currency' => 'USD'
            ),
            "pricing_type" => "fixed_price",
            "metadata" => array(
                'name' => $username
            )
        );

        // Initialize cURL session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.commerce.coinbase.com/charges');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'X-Cc-Api-Key: ' . $coinbase_api;
        $headers[] = 'X-Cc-Version: 2018-03-22';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Execute the cURL request
        $response = curl_exec($ch);
        curl_close($ch);

        // Parse the response
        $response = json_decode($response, true);
        $uqid = $response['data']['id'];
        $url = $response['data']['hosted_url'];

        $sql = "INSERT INTO `orders` (`user_id`, `amount`, `payment_method`, `status`, `link`, `uqid`, `type`) VALUES ('$id', '$amount', '$method', '1', '$url', '$uqid', 'deposit');";
        $result = mysqli_query($conn, $sql);

        header("Location: " . $url);
        exit();
    } elseif ($method == "Cashapp") {
        $note = cashapp_note();

        $sql = "INSERT INTO `orders` (`user_id`, `amount`, `payment_method`, `status`, `link`, `cashapp_note`, `type`) VALUES ('$id', '$amount', '$method', '1', 'order', '$note', 'deposit');";
        $result = mysqli_query($conn, $sql);

        // Get inserted order ID
        $order_id = mysqli_insert_id($conn);

        header("location: order.php?id=" . $order_id);
        exit();
    } else {
        // Handle the error condition
        $error = $response['error'];
        $error = urlencode($error);
        header("location: deposits.php?error=" . $error);
        exit();
    }
}



if (isset($error)) {
    echo "Error: " . $error;
}

?>



<!DOCTYPE html>

<html lang="en-US">

<head>
<html lang="en-US">
<link type="text/css" rel="stylesheet" id="dark-mode-custom-link">
<link type="text/css" rel="stylesheet" id="dark-mode-general-link">
<style lang="en" type="text/css" id="dark-mode-custom-style"></style>
<style lang="en" type="text/css" id="dark-mode-native-style"></style>
<style lang="en" type="text/css" id="dark-mode-native-sheet"></style>

<head>
    <?php include __DIR__ . "/../comp/header.php"; ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <title><?php echo $shop_title; ?> - Settings</title>
</head>

    <title>
        <?php echo $shop_title; ?> - Deposits
    </title>
    <style>
        table.table,
        table.table thead,
        table.table tbody {
            border-top: none !important;
        }
    </style>
</head>

<body>

    <main class="main" id="top">

        <?php include __DIR__ . "/../comp/nav.php"; ?>
        <div class="dashboard_main_sec">

            <div class="container">

                <div class="row">

                    <?php include __DIR__ . "/../comp/subnav.php";
                    ?>

                    <div class="col-12 col-md-9">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="dashboard_main_head text-center">
                                    <h5>Deposits</h5>
                                    <p>Deposit funds to your account</p>
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
                                <input type="hidden" name="act" value="add">
                                <div class="mb-3">
                                    <label for="name" class="form-label text-white">Amount:</label>
                                    <input type="text" class="form-control mb-3 text-white bg-dark" name="amount"
                                        id="amount" placeholder="Amount" required>
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="text-white form-label">Method:</label>
                                    <select class="form-control mb-3 text-white bg-dark" name="method" id="method"
                                        required>
                                        <?php
                                        $sql = "SELECT hoodpay, coinbase, cashapp FROM site_settings WHERE id = 1";
                                        $result = mysqli_query($conn, $sql);

                                        if ($result) {
                                            $row = mysqli_fetch_assoc($result);

                                            // Check each setting and generate an option if its value is 1
                                            if ($row['hoodpay'] == 1) {
                                                echo '<option value="Hoodpay">Hoodpay</option>';
                                            }

                                            if ($row['coinbase'] == 1) {
                                                echo '<option value="Coinbase">Coinbase</option>';
                                            }

                                            if ($row['cashapp'] == 1) {
                                                echo '<option value="Cashapp">Cashapp</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-danger w-100">Submit</button>
                            </form>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="dashboard_main_head" style="text-align: center;">
                                    <h5>All Deposits</h5>
                                    <p>View all your deposits</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="dash_recent_order py-0 col-lg-12">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th class="px-4 py-2 border-0">ID</th>
                                                <th class="px-4 py-2 border-0">Unique ID</th>
                                                <th class="px-4 py-2 border-0">Amount</th>
                                                <th class="px-4 py-2 border-0">Method</th>
                                                <th class="px-4 py-2 border-0">Status</th>
                                                <th class="px-4 py-2 border-0">Date</th>
                                                <th class="px-4 py-2 border-0">Actions</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php
                                            $showlogs = mysqli_query($conn, "SELECT * FROM `orders` WHERE `user_id` = '$id' AND `type` = 'deposit' ORDER BY `id` DESC");
                                            while ($row = mysqli_fetch_array($showlogs)) {

                                                if ($row['payment_method'] == "Cashapp") {
                                                    $uqid = $id . '-' . $row['cashapp_note'];
                                                } else {
                                                    $uqid = $row['uqid'];
                                                }

                                                $link = ($row['payment_method'] == "Cashapp") ? 'order.php?id=' . $row['id'] : $row['link'];

                                                if ($row['status'] == 0) {
                                                    $status = '<span class="badge bg-danger">Unknown</span>';
                                                } elseif ($row['status'] == 1) {
                                                    $status = '<span class="badge bg-warning">Pending</span>';
                                                } elseif ($row['status'] == 2) {
                                                    $status = '<span class="badge bg-success">Completed</span>';
                                                } elseif ($row['status'] == 3) {
                                                    $status = '<span class="badge bg-danger">Failed</span>';
                                                } elseif ($row['status'] == 4) {
                                                    $status = '<span class="badge bg-info">New</span>';
                                                }

                                                echo '
                                                <tr class="names" data-name="' . $row['id'] . '">
                                                    <td class="px-4 py-2 border-0">' . $row['id'] . '</td>
                                                    <td class="px-4 py-2 border-0">' . $uqid . '</td>
                                                    <td class="px-4 py-2 border-0">' . $row['amount'] . '</td>
                                                    <td class="px-4 py-2 border-0">' . $row['payment_method'] . '</td>
                                                    <td class="px-4 py-2 border-0">' . $status . '</td>
                                                    <td class="px-4 py-2 border-0">' . date("j M, G:i", strtotime($row['created_at'])) . '</td>
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

    <?php include __DIR__ . "/../comp/footer.php"; ?>

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