<?php

session_start();



require_once __DIR__ . "/../server/db.php";
require_once __DIR__ . "/../comp/functions.php";



$role = 0;
if (isset($_SESSION["loggedin"])) {

    $id = $_SESSION["id"];
    $username = $_SESSION["username"];
    $role = intval(getUserData($id, "role"));
    $bal = getUserData($id, "bal");
}


if (isset($_GET['remove'])) {

    $prod_id = mysqli_real_escape_string($conn, $_GET['remove']);
    unset($_SESSION['cart'][$prod_id]);
    header("Location: ./cart.php?success=Product removed from cart");
    exit();

}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['coupon']) && !empty($_POST['coupon'])) {

    $coupon = mysqli_real_escape_string($conn, $_POST['coupon']);
    $sql = "SELECT * FROM `coups` WHERE `code` = '$coupon' AND `status` = 1";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) == 0) {
        $couponStatus = 0;
    } else {
        $discount = mysqli_fetch_assoc($result);
        $discount = $discount['discount'];

        $couponStatus = 1;
    }
}


$stocks = array();
//get all stocks
$sql = "SELECT `prod_id`, COUNT(id) as num FROM `stock` GROUP BY `prod_id` ORDER BY `prod_id` ASC;";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $prod_id = $row['prod_id'];
    $stocks[$prod_id] = $row['num'];
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
        <?php echo $shop_title; ?> - Cart
    </title>
<style>

.crypto-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr); /* 2 buttons in the first row */
    gap: 10px;
    padding: 20px;
}

.crypto-grid button:last-child {
    grid-column: 1 / span 2; /* Center the last button on a new row */
}

.crypto_btn {
    background-color: transparent;
    color: white;
    border: 2px solid transparent;
    padding: 10px 15px;
    border-radius: 5px;
    font-size: 16px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative; /* Needed for z-index to take effect */
    z-index: 1;
}

.crypto_btn:hover, .crypto_btn:focus {
    background-color: rgba(165, 42, 42, 0.2); /* Slight background color on hover */
    border-color: #a52a2a; /* Red border on hover and focus */
}

.crypto_btn:active, .crypto_btn.selected {
    background-color: rgba(165, 42, 42, 0.4); /* Darker background for active/selected state */
    border-color: #a52a2a; /* Red border for active/selected state */
}

/* Responsive adjustments for smaller screens */
@media (max-width: 768px) {
    .crypto-grid {
        grid-template-columns: 1fr; /* Stack buttons vertically on smaller screens */
    }

    .crypto-grid button:last-child {
        grid-column: 1; /* Ensure the button fills the column */
    }
}


</style>
</head>

<body>

    <div class="glow-corner bottom-left"></div>
    <div class="glow-corner top-right"></div>
    <main class="main" id="top">

        <?php
        include __DIR__ . "/../comp/nav.php";

        $result = mysqli_query($conn, "SELECT home_announcement FROM site_settings");
        $row = mysqli_fetch_assoc($result);
        $homeAnnouncement = !empty($row['home_announcement']) ? $row['home_announcement'] : '';

        $result2 = mysqli_query($conn, "SELECT home_announcement_link FROM site_settings");
        $row2 = mysqli_fetch_assoc($result2);
        $homeAnnouncementlink = !empty($row2['home_announcement_link']) ? $row2['home_announcement_link'] : '';
        ?>

        <?php if (!empty($homeAnnouncement)): ?>
            <div class="head_notify_box text-center mb-4 px-4 mx-auto w-50"
                style="border: 1px solid #f91d03; background-color: rgba(0, 0, 0, 0.5);">
                <p class="justify-content-center">
                    <a href="<?php echo $homeAnnouncementlink; ?>" class="text-white">
                        <?php echo $homeAnnouncement; ?>
                    </a>
                </p>
            </div>
        <?php endif; ?>


        <div class="container">



            <section class="cart_section">

                <div class="container">

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

                        <div class="col-lg-7">

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="cart_head">
                                        <h3>Your Cart</h3>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="heading_btn">
                                        <a href="../shop/index.php">Explore More Products<img
                                                src="../assets/images/arrw_rgt.svg" alt="a" class="img-fluid"></a>
                                    </div>
                                </div>
                            </div>





                            <?php
                            $total = 0;
                            if (isset($_SESSION['cart']) && is_array($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
                                $items_count = count($_SESSION['cart']);
                                foreach ($_SESSION['cart'] as $prod_id => $array) {

                                    $prod_id = $array['id'];
                                    $quantity = $array['quantity'];

                                    //check quantity
                                    if (!isset($stocks[$prod_id])) {
                                        $stocks[$prod_id] = 0;
                                    }

                                    if ($quantity > $stocks[$prod_id]) {
                                        $quantity = $stocks[$prod_id];
                                        $_SESSION['cart'][$prod_id]['quantity'] = $quantity;
                                    }

                                    if ($quantity == 0) {
                                        unset($_SESSION['cart'][$prod_id]);
                                        continue;
                                    }

                                    $prod = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM prods WHERE id = '$prod_id'"));
                                    $prod_name = $prod['title'];
                                    $prod_price = $prod['price'];
                                    $prod_img = $prod['img'];

                                    $total = $total + $prod_price * $quantity;

                                    $min = $prod['min'];


                                    if ($min > $quantity) {
                                        $quantity = $min;
                                        $_SESSION['cart'][$prod_id]['quantity'] = $quantity;
                                    }
                                    echo '
                            
                            
                            
                            
                            
                            <div class="cart_item_lists_wraps">
                                    <input type="hidden" id="prod_id' . $prod_id . '" value="' . $prod_id . '">
                                    <input type="hidden" id="prod_price' . $prod_id . '" value="' . $prod_price . '">

                                <div class="cart_item_lft_img">
                                    <img src="' . $prod_img . '" alt="' . $prod_name . '" class="img-fluid">
                                </div>
                                <div class="cart_item_title">
                                    <h5>' . $prod_name . '</h5>
                                    <p>Min: ' . $min . ' - Max: ' . $stocks[$prod_id] . '</p>
                                </div>
                                <div class="cart_item frm_cntrl">
                                    <input id="quant' . $prod_id . '" type="number" class="form-control quant" value="' . $quantity . '" max="' . $stocks[$prod_id] . '" style="padding: 5px !important;height: 35px;width: 100px !important;" min="' . $min . '" >
                                </div>

                                <div class="cart_item">
                                <h5><span class="text-danger prices" id="price'.$prod_id.'">$'.number_format($prod_price * $quantity, 2).'</span></h5>
                                
                                <small class="text-white" id="price_per' . $prod_id . '">$' . $prod_price . ' x ' . $quantity . '</small>
                                </div>
                                <div class="cart_close">
                                    <form method="POST">
                                        <a type="submit" name="Action" value="RemoveItem" class="btn btn-link p-0 d-block" href="./cart.php?remove=' . $prod_id . '"><span class="fa fa-times text-muted"></span></a>
                                    </form>
                                </div>
                            </div>
                            
                            ';

                                }
                            } else {
                                $items_count = 0;
                                echo '<div class="py-5 text-center">
                        <h3 class="text-white pt-5">oops! you dont have anything in cart</h3>
                        <p class="font-weight-light text-muted pb-5">click explore more and add more whoohoo!!</p>
                    </div>';
                            }

                            ?>



                            <div class="row mt-4">

                                <div class="col-lg-6">
                                    <?php

                                    if (isset($couponStatus) && $couponStatus == 1) {
                                        echo '<div class="badge badge-success">Coupon applied</div>';
                                    } else if (isset($couponStatus) && $couponStatus == 0) {
                                        echo '<div class="badge badge-danger">Coupon invalid</div>';
                                    }

                                    ?>

                                    <form method="POST" class="copun_enter_input">


                                        <input type="text" id="coupon" name="coupon" style="border: #FFF !important;"
                                            placeholder="Coupon Code" value="<?php if (isset($coupon)) {
                                                echo $coupon;
                                            } ?>">
                                        <button type="submit" style="background: #f91d03; border: none !important;"
                                            name="Action" value="ApplyCoupon">Apply</button>

                                    </form>



                                </div>

                                <div class="col-lg-6">

                                    <div class="subtotal_tab">
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <td>Subtotal:</td>
                                                    <td class="total_all_price">$
                                                        <?php echo $total; ?>
                                                    </td>
                                                </tr>
                                                <?php

                                                if (isset($couponStatus) && $couponStatus == 1) {
                                                    $total = round($total - $total * $discount / 100, 2);
                                                    echo '<tr>
                                                                    <td>Discount:</td>
                                                                    <td id="discount_value">' . $discount . '%</td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="color: #f91d03;">Total :</td>
                                                                    <td id="discounted_total">$' . $total . '</td>
                                                                </tr>';
                                                } else {
                                                    $total = ($total - $total * 0 / 100);
                                                    echo '<tr>
                                                                <td>Discount:</td>
                                                                <td id="discount_value">0%</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: #f91d03;">Total :</td>
                                                                <td id="discounted_total">$' . $total . '</td>
                                                            </tr>';
                                                }

                                                ?>

                                            </tbody>
                                        </table>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="col-lg-5">



                            <input type="hidden" name="Action" value="Checkout">

                            <div class="cart_invo_wrap mb-4">

                                <div class="checkout_user_wrap">



                                    <h6>Your Details</h6>
                                    <?php
                                    if (isset($_SESSION['loggedin'])) {
                                        echo '<div class="form-group">
                                                        <img style="margin-left: 10px !important;" src="../assets/images/user.svg" alt="a" class="img-fluid">
                                                        <input type="text" name="Email" id="email" value="' . $username . '" class="form-control text-center" readonly="" disabled>
                                                        <i class="fa fa-check"></i>
                                                    </div>

                                                    <div class="form-group frm_grp_2">
                                                        <input type="text" value="$' . $bal . '" class="form-control" readonly="" style="    padding-left: 21px;  !important; background: rgba(0, 0, 0, 0.5);">
                                                        <a href="../user/deposits.php"><i class="fa fa-plus"></i> Add Balance</a>
                                                    </div>
                                                    
                                                    

                                                    ';
                                    } else {
                                        echo '<p>Create an account for faster support.</p>

                                                <div class="log_btns">
                                                    <a href="../auth/register.php">Create Account</a>
                                                    <a href="../auth/login.php">Log In</a>
                                                </div>
            
                                                <div class="d-block mt-4">
                                                    <img src="../assets/images/or.svg" alt="OR" class="img-fluid">
                                                </div>
            
                                                <p>Enter your email to recieve products</p>
                                                <div class="frm_cntrl">
                                                    <input type="email" id="email" name="email" placeholder="Email" 
                                                        class="form-control" value="" required="">
                                                    <img src="../assets/images/mail_icon.svg" alt="Mail" class="img-fluid">
                                                </div>
                                                ';
                                    }
                                    ?>

                                </div>


                            </div>

                            <div class="cart_invo_wrap">

                                <h6 class="pb-3">Payment Method</h6>

                                <div class="row">

                                    <?php

                                    $sql = "SELECT * FROM site_settings WHERE id = 1";

                                    $result = mysqli_query($conn, $sql);

                                    if ($result) {
                                        $row = mysqli_fetch_assoc($result);

                                        $coinbaseValue = $row['coinbase'];
                                        $cashappValue = $row['cashapp'];
                                        $hoodpayValue = $row['hoodpay'];
                                        $balanceValue = $row['balance'];


                                        if ($coinbaseValue == 1) {

                                            echo '<div class="col-6 pr-0">
                <label class="payment_boxs_wrap">
                    <input type="radio" name="payment_method" value="Coinbase" required="" data-captcha="0" class="checkout-processor" style="opacity:0;">
                    <p>Coinbase</p>
                </label>
            </div>';
                                        }

                                        if ($cashappValue == 1) {
                                            // Show the Cashapp div
                                            echo '<div class="col-6 pr-0">
                <label class="payment_boxs_wrap">
                    <input type="radio" name="payment_method" value="Cashapp" required="" data-captcha="0" class="checkout-processor" style="opacity:0;">
                    <p>Cashapp</p>
                </label>
            </div>';
                                        }

                                        if ($hoodpayValue == 1) {
                                            echo '<div class="col-6 pr-0">
                                            <label class="payment_boxs_wrap">
                                                <input type="radio" name="payment_method" value="Poof" required="" data-captcha="0" class="checkout-processor" style="opacity:0;">
                                                <p>Crypto</p>
                                            </label>
                                            <input type="hidden" name="crypto_type" id="crypto_type" value=""> <!-- Hidden input for crypto type -->
                                        </div>';
                                        }

                                        if ($balanceValue == 1 && isset($_SESSION['loggedin'])) {
                                            echo '<div class="col-6 pr-0">
                <label class="payment_boxs_wrap">
                    <input type="radio" name="payment_method" value="Balance" required="" data-captcha="0" class="checkout-processor" style="opacity:0;">
                    <p>Balance</p>
                </label>
            </div>';
                                        }
                                    }

                                    ?>

                                </div>

                                <ul class="mt-4">

                                </ul>


                                <img src="../assets/images/line.svg" alt="a" class="img-fluid d-block py-4">

                                <div class="total_invo_btn">
                                    <span>
                                        <p>TOTAL </p>
                                        <h5 class="total_all_price">$
                                            <?php echo $total; ?>
                                        </h5>
                                    </span>
                                    <form class="text" id="payForm" method="POST">
                                        <div class="text d-flex">
                                            <button type="submit" class="btn btn_submit" value="Pay Now"
                                                style="width: 100%;">Checkout <img src="../assets/images/arrw_rgt.svg"
                                                    alt="a" class="img-fluid"></button>
                                        </div>
                                    </form>
                                </div>
                            </div>


                        </div>

                        <div class="proceesd_btnns">
                            <a href="../shop/terms.php">By proceeding, you agree to our <u>Terms &amp;
                                    Conditions</u></a>
                        </div>



                    </div>

                </div>





            </section>

        </div>


    </main>

    <?php
    include __DIR__ . "/../comp/footer.php";
    ?>

    <a href="#" class="back-top-btn">
        <i class="fa fa-angle-up"></i>
    </a>

    <div class="modal fade" id="buy-now-modal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content mdl">
                <div class="modal-header align-items-center p-2">
                    <h3 class="text-white font-weight-light mb-0" id="buy-now-title">Buy Now</h3>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>×</span>
                    </button>
                </div>
                <div class="modal-body p-2">
                    <div class="p-5 text-center" id="buy-now-loader">
                        <div class="spinner-grow text-brand" style="height: 5rem; width: 5rem;"></div>
                    </div>
                    <div id="buy-now-content"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/script.js"></script>


    <script>
        $(document).ready(function () {

            $("input[name='payment_method']").change(function () {
        var paymentMethod = $(this).val();
        if (paymentMethod === "Poof") {
            var cryptoButtons = '<div id="crypto_options" class="crypto-grid">' +
            '<button class="crypto_btn" data-crypto="bitcoin">Bitcoin</button>' +
            '<button class="crypto_btn" data-crypto="litecoin">Litecoin</button>' +
            '<button class="crypto_btn" data-crypto="ethereum">Ethereum</button>' +
            '</div>';
            $("#crypto_options").remove();
            $(this).closest('.payment_boxs_wrap').after(cryptoButtons);
        } else {
            $("#crypto_options").remove();
            $("#crypto_type").val('');
        }
    });

    $(document).on("click", ".crypto_btn", function () {
        var cryptoType = $(this).data("crypto");
        $("#crypto_type").val(cryptoType);
    });

            $(".quant").change(function () {
                var id = $(this).attr('id').replace('quant', '');
                var quant = $(this).val();
                var price = $("#prod_price" + id).val();
                //get max attribute
                var max = $(this).attr('max');
                var min = $(this).attr('min');



                quant = parseInt(quant);

                if (quant > max) {
                    quant = max;
                }

                if (quant < min) {
                    quant = min;
                }



                $("#price" + id).html("$" + (price * quant));
                $(this).val(quant);

                //update total all price
                var total = 0;
                $(".prices").each(function () {
                    var total_price = $(this).html();

                    //remove $ from total_price
                    total_price = total_price.replace('$', '');



                    total = parseInt(total) + parseInt(total_price);
                });

                console.log(total);

                $(".total_all_price").html("$" + total);

                //update discount
                var discount = $("#discount_value").html();
                discount = discount.replace('%', '');
                discount = parseInt(discount);

                let discounted_total = total - total * discount / 100;
                discounted_total = discounted_total.toFixed(2);

                //if last 2 digits are 0
                if (discounted_total.slice(-2) == "00") {
                    discounted_total = discounted_total.slice(0, -3);
                }
                $("#discounted_total").html("$" + discounted_total);


                //send ajax to update cart
                $.ajax({
                    url: './cart_api.php',
                    method: 'POST',
                    data: {
                        prod_id: id,
                        quantity: quant
                    }
                });

            });


            $("#payForm").submit(function (e) {
                e.preventDefault();
                var email = $("#email").val();
                var payment_method = $("input[name='payment_method']:checked").val();
                var coupon = $("#coupon").val();
                var crypto_type = $("#crypto_type").val();

                var form = $('<form action="../buy.php" method="post">' +
            '<input type="hidden" name="email" value="' + email + '" />' +
            '<input type="hidden" name="payment_method" value="' + payment_method + '" />' +
            '<input type="hidden" name="coupon" value="' + coupon + '" />' +
            '<input type="hidden" name="crypto_type" value="' + crypto_type + '" />' +
            '</form>');
                $('body').append(form);
                form.submit();

            });
        });
    </script>

    <style>
        .btn-float-discord {
            position: fixed;
            width: 60px;
            height: 60px;
            bottom: 180px;
            right: 25px;
            padding: 13px 15px 15px 15px;
            background-color: #5865f2;
            color: #ffffff;
            font-weight: 400;
            border: 0;
        }

        .btn-float-telegram {
            position: fixed;
            width: 60px;
            height: 60px;
            bottom: 100px;
            right: 25px;
            padding: 13px 15px 15px 10px;
            background-color: #28a7ea;
            color: #ffffff;
            border: 0;
        }

        @media only screen and (max-width: 768px) {

            .btn-float-discord,
            .btn-float-telegram {
                padding: 10px;
                right: 12px;
                width: 54px;
                height: 54px;
            }

        }
    </style>




</body>

</html>