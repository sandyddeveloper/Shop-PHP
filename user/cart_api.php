<?php

session_start();

require_once __DIR__ . "/../server/db.php";
require_once __DIR__ . "/../comp/functions.php";

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){

    $prod_id = mysqli_real_escape_string($conn, $_POST['prod_id']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);


    if(!isset($_SESSION['cart'])){
        $_SESSION['cart'] = array();
    }

    
    $cart_items = $_SESSION['cart'];

    $cart_items[$prod_id] = array(
        'id' => $prod_id,
        'quantity' => $quantity
    );

    
    $_SESSION['cart'] = $cart_items;

    echo '1';
}


?>