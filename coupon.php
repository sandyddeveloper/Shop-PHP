<?php

require_once('server/db.php');
require_once 'comp/functions.php';

//check if request is from ajax
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){

    //check if coupon is valid
    $coupon = mysqli_real_escape_string($conn, $_POST['coupon']);
    $sql = "SELECT * FROM `coups` WHERE `code` = '$coupon' AND `status` = 1";
    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result) == 0){
        $response = array(
            "status" => false,
            "message" => "Invalid coupon"
        );
        echo json_encode($response);
        exit();
    }
    $coupon = mysqli_fetch_assoc($result);
    $discount = $coupon['discount'];


    //return coupon value
    $response = array(
        "status" => true,
        "discount" => $discount
    );
    echo json_encode($response);
    exit();

}
?>