<?php

require_once('./server/db.php');

$sending_email = "no-reply@duffshop.io";
$webstite_base = "https://duffshop.io/index.php";
$shop_name = "duffshop";
$website_logo = "https://duffshop.io/images/logo_duff.png";

$sql = "SELECT * FROM users";
$result = mysqli_query($conn, $sql);

    $test = "no-reply@duffshop.io";

while ($row = mysqli_fetch_assoc($result)) {
    $to = $row['email'];
    $username = $row['username'];
    

 
    

    $subject = "Happy New Year! Here is Your Gift | From $shop_name";

    // Generate a random coupon code (customize this logic as needed)
    $couponCode = "ILoveduff";

    // Construct the HTML email message
$message = <<<HTML
<!doctype html>
<html lang="en-US">

<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />

    <style type="text/css">
        a:hover {text-decoration: underline !important;}
    </style>
</head>

<body marginheight="0" topmargin="0" marginwidth="0" style="margin: 0px; background-color: #f2f3f8;" leftmargin="0">
    <!-- 100% body table -->
    <table cellspacing="0" border="0" cellpadding="0" width="100%" bgcolor="#f2f3f8"
        style="@import url(https://fonts.googleapis.com/css?family=Rubik:300,400,500,700|Open+Sans:300,400,600,700); font-family: 'Open Sans', sans-serif;">
        <tr>
            <td>
                <table style="background-color: #f2f3f8; max-width:670px; margin:0 auto;" width="100%" border="0"
                    align="center" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="height:80px;">&nbsp;</td>
                    </tr>
                  
                    <tr>
                        <td style="height:20px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>
                            <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0"
                                style="max-width:670px; background:#fff; border-radius:3px; text-align:center;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);">
                                <tr>
                                    <td style="height:40px;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding:0 35px;">
                                        <h1 style="color:#1e1e2d; font-weight:500; margin:0;font-size:32px;font-family:'Rubik',sans-serif;">Happy New Year!
                                        </h1>
                                        <p style="font-size:15px; color:#455056; margin:8px 0 0; line-height:24px;">
                                            Wishing you a joyful and prosperous New Year! Here is a special gift for you $username </p>
                                        <span
                                            style="display:inline-block; vertical-align:middle; margin:29px 0 26px; border-bottom:1px solid #cecece; width:100px;"></span>
                                        <p
                                            style="color:#455056; font-size:30px;line-height:20px; margin:0; font-weight: 500;">
                                            <strong
                                                style="display: block;font-size: 13px; margin: 0 0 4px; color:rgba(0,0,0,.64); font-weight:normal;"><span style="font-weight: bold; font-size: 24px;">$couponCode</span></strong>

                                        </p>
                                        <br> <br>

                                        <p
                                            style="font-size:15px; color:#455056; margin:8px 0 0; line-height:24px;">
                                            Use the following coupon code to get a discount on your next purchase</p>

                                       <br> <br>
                      <a href="$webstite_base" style="color: #20e277; text-decoration: underline;">Click Here To buy!</a>


                                    </td>
                                </tr>
                                <tr>
                                    <td style="height:40px;">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="height:20px;">&nbsp;</td>
                    </tr>

                    <tr>
                        <td style="height:80px;">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!--/100% body table-->

    
</body>

</html>
HTML;

    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    $headers .= 'From: ' . $test . "\r\n";
    $headers .= 'CC: ' . $test . "\r\n"; // Set from headers
    if (!empty($to)) {
        mail($to, $subject, $message, $headers); // Send our email
        echo "Email sent to: $to <br>";
    }
}

mysqli_close($conn);



?>