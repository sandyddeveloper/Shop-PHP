<?php
require_once './server/db.php';
require_once './comp/functions.php';


$sql = "SELECT * FROM site_settings WHERE id = 1"; // Adjust the query as needed
$result = mysqli_query($conn, $sql);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $shopName = $row['shop_name'];
} else {
    $shopName = "Shop Name Not Found"; 
}

echo $shopName;


mysqli_close($conn);
?>