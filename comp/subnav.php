<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <style>
        /* Add some styles for the button and mobile navigation */
        @media (max-width: 767px) {
            .toggle-btn {
                display: block;
                position: fixed;
                top: 10px;
                left: 10px;
                z-index: 2;
                background-color: #006dc7;
                color: white;
                border: none;
                cursor: pointer;
                font-size: 20px;
                padding: 10px;
                border-radius: 5px;
            }

            .why_add_balnce_wrap {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 80%;
                max-width: 300px;
                background-color: #131313;
                padding: 10px;
                box-sizing: border-box;
                height: 100%;
                overflow-y: auto;
                z-index: 1;
            }

            .why_add_balnce_wrap.show-nav {
                display: block;
            }
        }
    </style>
</head>

<body>
    <div class="col-3 sm:w-full md:w-1/3 lg:w-1/4" id="responsiveNav">
        <!-- Mobile Toggle Button -->
        <button class="toggle-btn">&#9776;</button>

        
        <div class="why_add_balnce_wrap mt-0" >
            <h6 class="text-lg  mt-2" >Control Panel</h6>
            <div class="heading_btn space-y-2">
                <a href="../user/myorders.php" class="nav-link w-full text-center active-link">History</a>
                <a href="../user/deposits.php" class="nav-link w-full text-center active-link">Add funds</a>
                <a href="../user/settings.php" class="nav-link w-full text-center active-link">Settings</a>
            </div>

            <?php if ($role == 1) { ?>
                <h6 class="text-lg mt-4">Admin Panel</h6>
                <div class="heading_btn space-y-2">
                    <a href="../admin/admin.php" class="nav-link w-full text-center active-link">Home</a>
            <a href="../admin/add_cat.php" class="nav-link w-full text-center active-link">Categories</a>
            <a href="../admin/add_group.php" class="nav-link w-full text-center active-link">Groups</a>
            <a href="../admin/add_products.php" class="nav-link w-full text-center active-link">Products</a>
            <a href="../admin/add_users.php" class="nav-link w-full text-center active-link">Users</a>
            <a href="../admin/add_orders.php" class="nav-link w-full text-center active-link">Orders</a>
            <a href="../admin/add_depo.php" class="nav-link w-full text-center active-link">Deposits</a>
            <a href="../admin/add_support.php" class="nav-link w-full text-center active-link">Support</a>
            <a href="../admin/add_coup.php" class="nav-link w-full text-center active-link">Coupons</a>
            <a href="../admin/add_settings.php" class="nav-link w-full text-center active-link">Settings</a>
                </div>

                <h6 class="text-lg mt-4 ">Manager</h6>
                <div class="heading_btn space-y-2">
             <a href="../admin/add_ads.php" class="nav-link w-full text-center active-link">Manage Ads</a>
            <a href="../admin/add_faq.php" class="nav-link w-full text-center active-link">Manage faq</a>
            <a href="../admin/add_tos.php" class="nav-link w-full text-center active-link">Manage TOS</a>
            <a href="../admin/add_gif.php" class="nav-link w-full text-center active-link">Manage Gifs</a>
                </div>
            <?php } ?>

            <?php if ($role == 2) { ?>
                <h6 class="text-lg mt-4 ">Support Panel</h6>
                <div class="heading_btn space-y-2">
                    
                    <a href="../admin/add_orders.php" class="nav-link w-full text-center active-link">Orders</a>
                    <a href="../admin/add_support.php" class="nav-link w-full text-center active-link">Support</a>
                   <a href="../admin/add_users.php" class="nav-link w-full text-center active-link">Users</a>
                 <a href="../admin/add_reviews.php" class="nav-link w-full text-center active-link">Reviews</a>
            <a href="../admin/add_depo.php" class="nav-link w-full text-center active-link">Deposits</a>
                </div>
            <?php } ?>

            <?php if ($role == 3) { ?>
                <h6 class="text-lg mt-4 ">Restocker Panel</h6>
                <div class="heading_btn space-y-2">
                <a href="../admin/add_cat.php" class="nav-link w-full text-center active-link">Categories</a>
            <a href="../admin/add_group.php" class="nav-link w-full text-center active-link">Groups</a>
            <a href="../admin/add_products.php" class="nav-link w-full text-center active-link">Products</a>
                </div>
            <?php } ?>
        </div>
    </div>

    <script>
        // Toggle mobile navigation overlay
        $(document).ready(function () {
            $('.toggle-btn').on('click', function () {
                $('.why_add_balnce_wrap').toggleClass('show-nav');
            });

            // Close the mobile menu when clicking outside of it
            $(document).on('click', function (e) {
                if (!$(e.target).closest('.why_add_balnce_wrap').length && !$(e.target).is('.toggle-btn')) {
                    $('.why_add_balnce_wrap').removeClass('show-nav');
                }
            });
        });
    </script>
</body>

</html>
