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
$bal = getUserData($id, "bal");

if ($role != 1 && $role != 2) {
    header("location: ../user/dashboard.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <?php include(__DIR__ . '/../comp/header.php'); ?>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <title><?php echo $shop_title; ?> - Products Manager</title>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.bundle.min.js"></script>

    <style>
        body { overflow-x: hidden; }
        .form-control, .btn { margin-bottom: 10px; }
        .alert { margin-top: 20px; }
        .table { width: 100%; margin-top: 20px; }
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
                        <div class="dashboard_main_head text-center">
                            <h5>Products Manager</h5>
                            <p>Manage your products</p>
                        </div>

                        <!-- Product Form for Add/Edit -->
                        <form id="productForm">
                            <div class="mb-3">
                                <label for="title" class="form-label text-white">Title</label>
                                <input type="text" class="form-control text-white bg-dark" id="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="dsc" class="form-label text-white">Description</label>
                                <textarea class="form-control text-white bg-dark" id="dsc" required></textarea>
                            </div>
                            <div class="mb-3">
    <label for="cat_id" class="form-label text-white">Category</label>
    <select class="form-control text-white bg-dark" id="cat_id" required>
    </select>
</div>

                            <div class="mb-3">
                                <label for="price" class="form-label text-white">Price</label>
                                <input type="text" class="form-control text-white bg-dark" id="price" required>
                            </div>
                            <div class="mb-3">
                                <label for="hide" class="form-label text-white">Hide</label>
                                <select class="form-control text-white bg-dark" id="hide" required>
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                         
                         
                            <div class="mb-3">
                                <label for="img" class="form-label text-white">Image URL</label>
                                <input type="text" class="form-control text-white bg-dark" id="img" required>
                            </div>
                            <div class="mb-3">
                                <label for="priority" class="form-label text-white">Priority</label>
                                <input type="number" class="form-control text-white bg-dark" id="priority" required>
                            </div>
                            <div class="mb-3">
                                <label for="min" class="form-label text-white">Minimum Quantity</label>
                                <input type="number" class="form-control text-white bg-dark" id="min" required>
                            </div>
                            <button type="submit" class="btn btn-danger">Submit</button>
                        </form>

                        <div id="alertPlaceholder"></div>

                        <div id="productsTable" class="mt-4">
                        </div>
                    </div>
                </div>
                <!-- Edit Product Modal -->


            </div>
        </div>
    </main>
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header">
        <h5 class="modal-title">Edit Product</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editProductForm">
        <div class="modal-body">
          <input type="hidden" id="editProductId">
          <div class="mb-3">
            <label for="editTitle" class="form-label">Title</label>
            <input type="text" class="form-control" id="editTitle" required>
          </div>
          <div class="mb-3">
            <label for="editDsc" class="form-label">Description</label>
            <textarea class="form-control" id="editDsc" required></textarea>
          </div>
          <div class="mb-3">
            <label for="editPrice" class="form-label">Price</label>
            <input type="text" class="form-control" id="editPrice" required>
          </div>
          <div class="mb-3">
            <label for="editHide" class="form-label">Hide</label>
            <select class="form-control" id="editHide" required>
              <option value="0">No</option>
              <option value="1">Yes</option>
            </select>
          </div>
          <!-- Add more fields as needed -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="updateProduct()">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
    <?php include(__DIR__ . '/../comp/footer.php'); ?>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script>
$(document).ready(function() {
    fetchProducts(); // Initial fetch for products
    fetchCategories();
    $('#productForm').submit(function(e) {
        e.preventDefault();
        submitProductForm(); // Handle product form submission
    });
});


function fetchCategories() {
    $.ajax({
        url: 'http://localhost:4042/getcatogery',
        type: 'POST',
        headers: { 'X-Encrypted-Key': 'depP6vyqo1YztHwpkKLogBD00dAm0oBZ' },
        success: function(categories) {
            // Assuming the API returns an array of categories with id and title
            let optionsHtml = categories.map(category => `<option value="${category.id}">${category.title}</option>`).join('');
            $('#cat_id').html(optionsHtml);
        },
        error: function(xhr, status, error) {
            console.error("Failed to fetch categories:", error);
            $('#cat_id').html('<option value="">Failed to load categories</option>');
        }
    });
}


// Function to fetch and display products
function fetchProducts() {
    $.ajax({
        url: 'http://localhost:4042/getproducts',
        type: 'POST',
        headers: { 'X-Encrypted-Key': 'depP6vyqo1YztHwpkKLogBD00dAm0oBZ' },
        success: function(data) {
            let tableHtml = `
            <div class="row">
                <div class="dash_recent_order py-0 col-lg-12">
                 <div class="table-responsive">
                 <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>`;

            data.forEach(product => {
                tableHtml += `
                    <tr>
                        <td class="px-4 py-2 border-0">${product.id}</td>
                        <td class="px-4 py-2 border-0">${product.title}</td>
                        <td class="px-4 py-2 border-0">${product.dsc}</td>
                        <td class="px-4 py-2 border-0">${product.price}</td>
                        <td class="px-4 py-2 border-0">
                            <button onclick="editProduct(${product.id})" class="btn btn-info">Edit</button>
                            <button onclick="deleteProduct(${product.id})" class="btn btn-danger">Delete</button>
                        </td>
                    </tr>`;
            });

            tableHtml += `</tbody></table></div></div></div>`;
            $('#productsTable').html(tableHtml);
        },
        error: function(error) {
            console.error("Error fetching products: ", error);
            $('#productsTable').html('<p class="text-white">Error fetching products. Please try again later.</p>');
        }
    });
}


// Function to submit the product form for adding or editing
function submitProductForm() {
    // Collect form data
    const productData = {
        title: $('#title').val(),
        dsc: $('#dsc').val(),
        cat_id: $('#cat_id').val(),
        price: $('#price').val(),
        hide: $('#hide').val(),
        img: $('#img').val(),
        priority: $('#priority').val(),
        min: $('#min').val()
    };

    $.ajax({
        url: 'http://localhost:4042/create_products', 
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(productData),
        headers: { 'X-Encrypted-Key': 'depP6vyqo1YztHwpkKLogBD00dAm0oBZ' },
        success: function(response) {
            displayAlert(response.message, 'success');
            fetchProducts(); // Reload products
            $('#productForm')[0].reset(); // Clear form
        },
        error: function(error) {
            displayAlert('Failed to submit the product. Please try again.', 'danger');
        }
    });
}

// Function to display alerts
function displayAlert(message, type) {
    const alertHTML = `<div class="alert alert-${type}" role="alert">${message}</div>`;
    $('#alertPlaceholder').html(alertHTML);
    setTimeout(() => { $('#alertPlaceholder').html(''); }, 3000);
}

function editProduct(productId) {
    // Example AJAX call to get product details (make sure to replace with your actual API call)
    $.ajax({
        url: `http://localhost:4042/getproduct/${productId}`,
        type: 'POST',
        headers: { 'X-Encrypted-Key': 'depP6vyqo1YztHwpkKLogBD00dAm0oBZ' },
        success: function(product) {
            // Assuming 'product' is the response with product details
            $('#editProductId').val(product.id);
            $('#editTitle').val(product.title);
            $('#editDsc').val(product.dsc);
            $('#editPrice').val(product.price);
            $('#editHide').val(product.hide);
            // Populate other fields as needed
            
            // Show the modal
            $('#editProductModal').modal('show');
        },
        error: function(error) {
            console.error("Error fetching product details: ", error);
            // Handle error
        }
    });
}


function deleteProduct(productId) {
    const deleteData = {
        "id": productId
    };

    $.ajax({
        url: 'http://localhost:4042/deleteproduct',
        type: 'POST',
        headers: {
            'X-Encrypted-Key': 'depP6vyqo1YztHwpkKLogBD00dAm0oBZ',
            'Content-Type': 'application/json' 
        },
        data: JSON.stringify(deleteData),
        success: function(response) {
            displayAlert('Product deleted successfully', 'success');
            fetchProducts(); 
        },
        error: function(xhr, status, error) {
            const errorMessage = xhr.status + ': ' + xhr.statusText
            console.error('Error - ' + errorMessage);
            displayAlert('Failed to delete the product. Please try again. ' + errorMessage, 'danger');
        }
    });
}

</script>

</body>
</html>

