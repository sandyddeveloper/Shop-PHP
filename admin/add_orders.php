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

if ($role != 1 && $role != 2){
    header("location: ../user/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <?php include(__DIR__ . '/../comp/header.php'); ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <title><?php echo $shop_title; ?> - Orders Manager</title>
    
    <style>
        .pagination li {
            list-style: none;
            display: inline-block;
        }

        .pagination a {
            padding: 0.5rem 1rem;
            margin: 0.25rem;
            background-color: #2d3748;
            color: #fff;
            text-decoration: none;
            border-radius: 0.375rem;
        }

        .pagination a:hover {
            background-color: #1a202c;
        }

        .pagination .active a {
            background-color: #4a5568;
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
        <?php include(__DIR__ . '/../comp/nav.php'); ?>
        <div class="dashboard_main_sec">
            <div class="container">
                <div class="row">
                    <?php include(__DIR__ . '/../comp/subnav.php'); ?>
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="dashboard_main_head text-center">
                                    <h5>Orders Manager</h5>
                                    <p>Here you can manage all the orders.</p>
                                </div>
                            </div>
                        </div>
                        <?php
                        if (isset($_GET['error'])){
                            $error = htmlspecialchars(urldecode($_GET['error']));
                            echo '<div class="alert alert-danger" role="alert">';
                            echo $error;
                            echo '</div>';
                        } elseif (isset($_GET['success'])){
                            $success = htmlspecialchars(urldecode($_GET['success']));
                            echo '<div class="alert alert-success" role="alert">';
                            echo $success;
                            echo '</div>';
                        }
                        ?>
                        <div class="col-lg-4 mb-3">
                            <label for="search">Search:</label>
                            <input type="text" id="search" class="form-control" placeholder="Enter ID, Username, Method, or Products">
                        </div>
                        <div class="row">
                           <div class="dash_recent_order py-0 col-lg-12">
                                 <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 border-0">ID</th>
                                            <th class="px-4 py-2 border-0">Username</th>
                                            <th class="px-4 py-2 border-0">email</th>
                                            <th class="px-4 py-2 border-0">Products</th>
                                            <th class="px-4 py-2 border-0">Cost</th>
                                            <th class="px-4 py-2 border-0">Status</th>
                                            <th class="px-4 py-2 border-0">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $recordsPerPage = 10;
                                        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                                        $offset = ($page - 1) * $recordsPerPage;

                                        $showlogs = mysqli_query($conn, "SELECT * FROM `orders` WHERE `type` = 'buy' ORDER BY `id` DESC");
                                        $totalPages = ceil(mysqli_num_rows($showlogs) / $recordsPerPage);
                                        $visiblePages = min($totalPages, 10);

                                        $start = max(min($page - floor($visiblePages / 2), $totalPages - $visiblePages + 1), 1);
                                        $end = min($start + $visiblePages - 1, $totalPages);

                                        while ($row = mysqli_fetch_array($showlogs)) {

                                            $prods_string = "";
$prodArr = json_decode($row['prod_ids'], true);

if (is_array($prodArr)) {
    foreach ($prodArr as $prod) {
        $prod_title = $prod['title'];
        $quantity = $prod['quantity'];
        $prods_string .= "[" . $quantity . "x " . $prod_title . "], ";
    }

    $prods_string = rtrim($prods_string, ", ");
} else {
    $decodedData = json_decode($row['prod_ids']);
    if ($decodedData && isset($decodedData->title)) {
        $prods_string = $decodedData->title;
    } else {
        $prods_string = "Invalid product data";
    }
}



                                            if ($row['user_id'] == 0) {
                                                $user_name = 'guest';
                                            } else {
                                                $user_name = getUserData($row['user_id'], "username");
                                            }

                                            $class = "";
                                            $status = "";
                                            if ($row['status'] == 0) {
                                                $status = '<span class="text-white badge bg-danger">Unknown</span>';
                                            } elseif ($row['status'] == 1) {
                                                $status = '<span class="text-white badge bg-warning">Pending</span>';
                                            } elseif ($row['status'] == 2) {
                                                $status = '<span class="text-white badge bg-success">Completed</span>';
                                            } elseif ($row['status'] == 3) {
                                                $status = '<span class="text-white badge bg-danger">Failed</span>';
                                            } elseif ($row['status'] == 4) {
                                                $status = '<span class="text-white badge bg-info">New</span>';
                                            }

                                            echo '
                                            <tr class="names" data-name="' . $row['id'] . '">
                                                <td class="px-4 py-2 border-0">' . $row['id'] . '</td>
                                                <td class="px-4 py-2 border-0">' . $user_name . '</td>
                                                <td class="px-4 py-2 border-0">' . $row['email'] . '</td>
                                                <td class="px-4 py-2 border-0">' . $prods_string . '</td>
                                                <td class="px-4 py-2 border-0">' . $row['amount'] . '</td>
                                                <td class="px-4 py-2 border-0"><button class="px-2 py-1 text-white ' . $class . '" disabled>' . $status . '</button></td>
                                                <td class="px-4 py-2 border-0">
                                                    <a href="../order.php?id=' . $row['id'] . '" class="download-href"><i class="fas fa-eye"></i></a>
                                                </td>
                                            </tr>
                                            ';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        </div>
                        <!-- Custom Pagination Links -->
                        <div class="row">
                            <div class="col-lg-12">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <!-- Start Button -->
                                        <?php if ($start > 1): ?>
                                            <li class="page-item">
                                                <a class="mx-2 px-4 py-2 rounded bg-gray-800 text-white hover:bg-gray-700" href="?page=1">Start</a>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Previous Button -->
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="mx-2 px-4 py-2 rounded bg-gray-800 text-white hover:bg-gray-700" href="?page=<?php echo ($page - 1); ?>">Previous</a>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Page Numbers -->
                                        <?php for ($i = $start; $i <= $end; $i++): ?>
                                            <li class="page-item <?php echo ($page == $i ? 'active' : ''); ?>">
                                                <a class="mx-2 px-4 py-2 rounded bg-gray-800 text-white hover:bg-gray-700" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <!-- Next Button -->
                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="mx-2 px-4 py-2 rounded bg-gray-800 text-white hover:bg-gray-700" href="?page=<?php echo ($page + 1); ?>">Next</a>
                                            </li>
                                        <?php endif; ?>

                                        <!-- End Button -->
                                        <?php if ($end < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="mx-2 px-4 py-2 rounded bg-gray-800 text-white hover:bg-gray-700" href="?page=<?php echo $totalPages; ?>">End</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
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

    <script>
    const search = document.getElementById("search");

    search.oninput = function () {
        var names = document.getElementsByClassName("names");

        for (var i = 0; i < names.length; i++) {
            names[i].style.display = "none";
        }

        let searchValue = search.value.toLowerCase();

        for (var i = 0; i < names.length; i++) {
            let id = names[i].getElementsByTagName("td")[0].innerText.toLowerCase();
            let username = names[i].getElementsByTagName("td")[1].innerText.toLowerCase();
            let method = names[i].getElementsByTagName("td")[4].innerText.toLowerCase();
            let products = names[i].getElementsByTagName("td")[3].innerText.toLowerCase();
            let email = names[i].getElementsByTagName("td")[2].innerText.toLowerCase();

            if (id.includes(searchValue) || username.includes(searchValue) || method.includes(searchValue) || products.includes(searchValue) || email.includes(searchValue)) {
                names[i].style.display = "table-row";
            }
        }
    };
</script>

</body>
</html>
