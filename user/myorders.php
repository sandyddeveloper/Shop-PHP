<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();


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

if ($role != 1 && $role != 0 && $role != 2 && $role != 3) {
    header("location: location: ../user/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en-US">

<head>
    <?php include __DIR__ . "/../comp/header.php";
    ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <title>
        <?php echo $shop_title; ?> - History
    </title>
</head>

<body style="margin: 0; padding: 0; overflow: hidden;">
    <main class="main" id="top" style="height: 100vh; overflow-y: auto; padding-right: 0 !important;">
        <?php include __DIR__ . "/../comp/nav.php"; ?>
        <div class="dashboard_main_sec" style="height: calc(100vh - 55px); overflow-y: auto;">
            <div class="container">
                <div class="row">
                    <?php include __DIR__ . "/../comp/subnav.php"; ?>
                    <div class="col-12 col-md-9">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="dashboard_main_head" style="text-align: center;">
                                    <h5>History</h5>
                                    <p>View your order history.</p>
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
                        <div class="row">
                            <div class="dash_recent_order py-0 col-lg-12">
                                <div class="overflow-x-auto">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th class="px-4 py-2 border-0">ID</th>
                                                <th class="px-4 py-2 border-0">Products</th>
                                                <th class="px-4 py-2 border-0">Method</th>
                                                <th class="px-4 py-2 border-0">Cost</th>
                                                <th class="px-4 py-2 border-0">Status</th>
                                                <th class="px-4 py-2 border-0">Date</th>
                                                <th class="px-4 py-2 border-0">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $showlogs = mysqli_query($conn, "SELECT * FROM `orders` WHERE `user_id` = '$id' AND `type` = 'buy' ORDER BY `id` DESC");
                                            while ($row = mysqli_fetch_array($showlogs)) {
                                                $prods_string = "";
                                                $prodArr = json_decode($row['prod_ids'], true);
                                                foreach ($prodArr as $prod) {
                                                    $prod_title = $prod['title'];
                                                    $quantity = $prod['quantity'];
                                                    $prods_string .= "[" . $quantity . "x " . $prod_title . "], ";
                                                }
                                                $prods_string = rtrim($prods_string, ",");
                                                $class = "";
                                                if ($row['status'] == 4) {
                                                    $status = "New";
                                                    $class = "text-white bg-purple-700 hover:bg-purple-800 focus:outline-none focus:ring-4 focus:ring-purple-300 font-medium rounded-full text-sm px-2 py-2.5 text-center mb-2 dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-900";
                                                } elseif ($row['status'] == 1) {
                                                    $status = "Pending";
                                                    $class = "text-white bg-yellow-400 hover:bg-yellow-500 focus:outline-none focus:ring-4 focus:ring-yellow-300 font-medium rounded-full text-sm px-2 py-2.5 text-center mr-2 mb-2 dark:focus:ring-yellow-900";
                                                } elseif ($row['status'] == 2) {
                                                    $status = "Completed";
                                                    $class = "text-white bg-green-700 hover:bg-green-800 focus:outline-none focus:ring-4 focus:ring-green-300 font-medium rounded-full text-sm px-2 py-2.5 text-center mr-2 mb-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800";
                                                } elseif ($row['status'] == 3) {
                                                    $status = "Failed";
                                                    $class = "text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 font-medium rounded-full text-sm px-2 py-2.5 text-center mr-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900";
                                                } elseif ($row['status'] == 0) {
                                                    $status = "Unknown";
                                                    $class = "text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 font-medium rounded-full text-sm px-2 py-2.5 text-center mr-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900";
                                                }
                                                echo '
    <tr class="names" data-name="' . $row['id'] . '">
        <td class="px-4 py-2 border-0">' . $row['id'] . '</td>
        <td class="px-4 py-2 border-0">' . $prods_string . '</td>
        <td class="px-4 py-2 border-0">' . $row['payment_method'] . '</td>
        <td class="px-4 py-2 border-0">' . $row['amount'] . '</td>
        <td class="px-4 py-2 border-0"><button class="px-2 py-1 text-white ' . $class . '" disabled>' . $status . '</button></td>
        <td class="px-4 py-2 border-0">' . date("j M, G:i", strtotime($row['created_at'])) . '</td>
        <td class="px-4 py-2 border-0">
            <a href="../order.php?id=' . $row['id'] . '" class="download-href"><i class="fas fa-eye"></i></a>
        </td>
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