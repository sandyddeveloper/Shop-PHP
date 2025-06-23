<?php
session_start();

require_once(__DIR__ . '/../server/db.php');
require_once(__DIR__ . '/../comp/functions.php');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit();
}

$id = $_SESSION["id"];
$username = $_SESSION["username"];
$role = intval(getUserData($id, "role"));

if ($role != 1 && $role != 2) {
    header("location: ../user/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $act = mysqli_real_escape_string($conn, $_POST['act']);

    $acts = array("edit", "del");
    if (!in_array($act, $acts)) {
        exitWithError("Invalid action");
    }

    if ($act == "del") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);

        $sql = "DELETE FROM `users` WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            exitWithSuccess("User deleted successfully");
        } else {
            exitWithError("Error deleting user");
        }
    }
    if ($act == "edit") {
         $editUserId = mysqli_real_escape_string($conn, $_POST['id']);

        if ($editUserId == $id) {
            exitWithError("You can't edit your own account.");
        }
        
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $bal = mysqli_real_escape_string($conn, $_POST['bal']);
        $id = mysqli_real_escape_string($conn, $_POST['id']);

        $sql = "UPDATE `users` SET `username` = '$username', `email` = '$email', `role` = '$role', `bal` = '$bal', `updated_by` = '$username' WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            exitWithSuccess("User updated successfully");
        } else {
            exitWithError("Error updating user");
        }
    }
}

// Pagination
$limit = 250; // Users per page
$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];
$total_pages = ceil($total_users / $limit);

// Custom Pagination Links
$start = max($page - 2, 1);
$end = min($page + 2, $total_pages);

?>

<!DOCTYPE html>

<html lang="en-US">

<head>
    <?php include(__DIR__ . '/../comp/header.php'); ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <title><?php echo $shop_title; ?> - Users Manager</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <head>
    <!-- ... other head elements ... -->
    <style>
        .custom-pagination .page-link {
            color: grey;
        }

        .custom-pagination .page-item.active .page-link {
            background-color: grey;
            border-color: grey;
            color: white;
        }
    </style>
</head>

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
                                    <h5>Users Manager</h5>
                                    <p>Manage users</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mt-5 mx-auto">
                            <input type="text" class="form-control" id="search" placeholder="Search by username or email">
                        </div>

                        <br> <br>

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

                        <div class="dash_recent_order py-0 col-lg-12">
                                                            <div class="table-responsive">

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 border-0">ID</th>
                                        <th class="px-4 py-2 border-0">Username</th>
                                        <th class="px-4 py-2 border-0">Email</th>
                                        <th class="px-4 py-2 border-0">IP</th>
                                        <th class="px-4 py-2 border-0">Role</th>
                                        <th class="px-4 py-2 border-0">Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    $showlogs = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC LIMIT $offset, $limit");
                                    while ($row = mysqli_fetch_array($showlogs)) {

                                        echo '
                                            <tr class="names" data-name="' . $row['username'] . '' . $row['email'] . '">
                                                <td class="px-4 py-2 border-0">' . $row['id'] . '</td>
                                                <td class="px-4 py-2 border-0">' . $row['username'] . '</td>
                                                <td class="px-4 py-2 border-0">' . $row['email'] . '</td>
                                                <td class="px-4 py-2 border-0">' . $row['ip'] . '</td>
                                                <td class="px-4 py-2 border-0">' . ($row['role'] == 1 ? "Admin" : "User") . '</td>
                                                <td class="px-4 py-2 border-0">
                                                    <button class="download-href" data-bs-toggle="modal" data-bs-target="#modal' . $row['id'] . '"><i class="fas fa-edit"></i></button>

                                                    <form method="post" style="display: inline;" id="deleteForm_' . $row['id'] . '">
                    <input type="hidden" name="id" value="' . $row['id'] . '">
                    <input type="hidden" name="act" value="del">
                    <button type="button" class="download-href" onclick="confirmDelete(' . $row['id'] . ')"><i class="fas fa-trash-alt"></i></button>
                </form>
                                                </td>
                                            </tr>';

                                        echo '<div class="modal fade" id="modal' . $row['id'] . '" tabindex="-1" aria-labelledby="modal' . $row['id'] . '" style="display: none;" aria-hidden="true">                
                                        <div class="modal-dialog" role="document">
                                            <form method="post">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit user</h5>
                                                    </div>
                                                    <input type="hidden" name="id" value="' . $row['id'] . '">
                                                    <input type="hidden" name="act" value="edit">

                                                    <div class="modal-body">

                                                        <div class="mb-3">
                                                            <label for="recipient-name" class="col-form-label">ID:</label>
                                                            <input class="form-control" type="text" value="' . $row['id'] . '" disabled>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="recipient-name" class="col-form-label">Username:</label>
                                                            <input class="form-control" name="username" type="text" value="' . $row['username'] . '">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="recipient-name" class="col-form-label">Email:</label>
                                                            <input class="form-control" name="email" type="text" value="' . $row['email'] . '">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="recipient-name" class="col-form-label">Balance:</label>
                                                            <input class="form-control" name="bal" type="text" value="' . $row['bal'] . '">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="recipient-name" class="col-form-label">Role:</label>
                                                            <select class="form-control" name="role">
                                                                <option value="1" ' . ($row['role'] == 1 ? "selected" : "") . '>Admin</option>
                                                                <option value="0" ' . ($row['role'] == 0 ? "selected" : "") . '>User</option>
                                                                <option value="2" ' . ($row['role'] == 2 ? "selected" : "") . '>Support</option>
                                                                <option value="3" ' . ($row['role'] == 3 ? "selected" : "") . '>Restocker</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
                                                        <button class="btn btn-primary" type="submit">Save</button>
                                                    </div>
                                                </div>
                                            </form> 
                                        </div>
                                    </div>';
                                   

 }
                                    ?>
                                </tbody>
                            </table>
                            </div>
                            <!-- Custom Pagination Links -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-center">
                                            <!-- Start Button -->
                                            <?php if ($start > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=1">Start</a>
                                                </li>
                                            <?php endif; ?>

                                            <!-- Previous Button -->
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo ($page - 1); ?>">Previous</a>
                                                </li>
                                            <?php endif; ?>

                                            <!-- Page Numbers -->
                                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                                <li class="page-item <?php echo ($page == $i ? 'active' : ''); ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <!-- Next Button -->
                                            <?php if ($page < $total_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo ($page + 1); ?>">Next</a>
                                                </li>
                                            <?php endif; ?>

                                            <!-- End Button -->
                                            <?php if ($end < $total_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo $total_pages; ?>">End</a>
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
        </div>
    </main>

    <?php include(__DIR__ . '/../comp/footer.php'); ?>

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/script.js"></script>
    
    <style>
    .swal-black {
    background-color: #333;
    color: #fff;
}

.swal-black-header,
.swal-black-title {
    color: #fff;
}

.swal-black-cancel-button {
    background-color: #666;
    color: #fff;
}

.swal-black-confirm-button {
    background-color: #d33;
    color: #fff;
}

</style>
    <script>
   function confirmDelete(categoryId) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You won\'t be able to revert this!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        customClass: {
            popup: 'swal-black', // Add a class for styling
            header: 'swal-black-header',
            title: 'swal-black-title',
            cancelButton: 'swal-black-cancel-button',
            confirmButton: 'swal-black-confirm-button',
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // If the user confirms, submit the corresponding form for deletion
            document.getElementById('deleteForm_' + categoryId).submit();
        }
    });
}

</script>

    <script>
        $(document).ready(function () {
            $('.select2').select2();

            //for each modal
            $('.select2Modal').select2();
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const search = document.getElementById("search");
            let allNames = document.getElementsByClassName("names");

            search.oninput = function () {
                const searchValue = search.value.trim().toLowerCase();

                for (let i = 0; i < allNames.length; i++) {
                    const id = allNames[i].getElementsByTagName("td")[0].innerText.toLowerCase();
                    const username = allNames[i].getElementsByTagName("td")[1].innerText.toLowerCase();
                    const email = allNames[i].getElementsByTagName("td")[2].innerText.toLowerCase();

                    const match =
                        id.includes(searchValue) ||
                        username.includes(searchValue) ||
                        email.includes(searchValue);

                    allNames[i].style.display = match ? "table-row" : "none";
                }
            };
        });
    </script>

</body>

</html>