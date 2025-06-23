<?php

require_once('server/db.php');
require_once 'comp/functions.php';



$json_input = file_get_contents('php://input');

$data = json_decode($json_input, true);

if (isset($data['payment_id'], $data['paid']) && $data['paid'] == "yes") {
    $payment_id = $data['payment_id'];

    $stmt = $conn->prepare("SELECT `id` FROM `orders` WHERE `uqid` = ?");
    $stmt->bind_param("s", $payment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $order_id = $row['id'];
        
        deliverOrder($order_id);
        
        echo "Order delivered successfully.";
    } else {
        echo "No matching order found.";
    }

    $stmt->close();
} else {
    echo "Payment not completed or missing data.";
}