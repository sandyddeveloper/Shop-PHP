<html>
    <head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="../assets/css/fontawesome.min.css">
<link rel="stylesheet" href="../assets/css/feather.css">

<link href="../assets/css/css2.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css">
<script src="../assets/js/l.js" async=""></script>
<script src="../assets/js/client.js" type="text/javascript" async=""></script>
<link href="../assets/css/client_default.css" type="text/css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
<meta name="title" content="polishstock.cc  | Best Account shop!">
<meta name="description" content="polishstock.cc - we here to provide cheap and best logs what will help you to make good amount of money and make yall rich ">
<meta name="robots" content="index, follow">
<meta name="keywords" content="Kohls, Kohls online shopping, Kohls coupons, Kohls clearance, Kohls promo codes, Kohls deals, Kohls in-store, Kohls credit card, Kohls Black Friday, Kohls rewards, Hotmail login, Hotmail sign up, Hotmail email, Hotmail account recovery, Hotmail password reset, Hotmail security, Hotmail support, Hotmail features, Hotmail two-step verification, Hotmail spam filter, Meijer grocery, Meijer weekly ad, Meijer pharmacy, Meijer curbside pickup, Meijer rewards, Meijer electronics, Meijer clothing, Meijer home goods, Meijer garden center, Meijer store locator, PayPal login, PayPal account, PayPal money transfer, PayPal business, PayPal fees, PayPal security, PayPal buyer protection, PayPal invoice, PayPal credit, PayPal mobile app, Taco Bell menu, Taco Bell locations, Taco Bell delivery, Taco Bell nutrition, Taco Bell online ordering, Taco Bell specials, Taco Bell promotions, Taco Bell rewards, Taco Bell app, Taco Bell gift cards, Office Depot near me, Office Depot online, Office Depot office furniture, Office Depot printing, Office Depot technology, Office Depot school supplies, Office Depot rewards program, Office Depot business solutions, Office Depot promo codes, Office Depot customer service, Victoria's Secret lingerie, Victoria's Secret bras, Victoria's Secret panties, Victoria's Secret sleepwear, Victoria's Secret beauty, Victoria's Secret swimwear, Victoria's Secret PINK, Victoria's Secret sale, Victoria's Secret clearance, Victoria's Secret Angel Card, Bath and Body Works candles, Bath and Body Works lotion, Bath and Body Works shower gel, Bath and Body Works aromatherapy, Bath and Body Works hand sanitizer, Bath and Body Works gifts, Bath and Body Works holiday, Bath and Body Works sale, Bath and Body Works coupons, Bath and Body Works rewards, Domino's pizza delivery, Domino's menu, Domino's specials, Domino's tracker, Domino's rewards, Domino's coupon codes, Domino's gluten-free pizza, Domino's contactless delivery, Domino's near me, Domino's customer service, Wendy's menu, Wendy's coupons, Wendy's value menu, Wendy's salads, Wendy's Frosty, Wendy's breakfast, Wendy's app, Wendy's drive-thru, Wendy's nutritional information, Wendy's customer feedback, Sonic drive-in, Sonic menu, Sonic Happy Hour, Sonic shakes, Sonic slushes, Sonic deals, Sonic app, Sonic nutrition, Sonic rewards, Sonic customer service,">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="language" content="English">
<!--<meta property="og:image" content="https://cap.fo/images/Now!.png" />-->
    <meta name="msapplication-TileColor" content="#a428c5" />
    <meta name="theme-color" content="#a428c5 " />
    <!--<link rel="icon" href="https://cap.fo/images/Now!.png">-->
    <!--<meta property="og:url" content="https://cap.fo/">-->
    <!--<meta name="twitter:card" content="https://cap.fo/images/Now!.png">-->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    
    
    
    
    
<!--Start of Tawk.to Script-->


</head>
<body>
<?php


require_once(dirname(__FILE__) . '/../server/db.php');
require_once(dirname(__FILE__) . '/../comp/functions.php');



$sql = "SELECT * FROM site_settings WHERE id = 1";

$result = mysqli_query($conn, $sql);

if ($result) {
    $row = mysqli_fetch_assoc($result);

if (!isset($row['shop_name']))
{
    $shop_title = "set on settings";
}
else {
    $shop_title = $row['shop_name'];
}

}



$sql = "SELECT `crisp` FROM `site_settings` WHERE `id`='1';";
$result = mysqli_query($conn, $sql);
$siteSettings = mysqli_fetch_assoc($result);
$crispKey = $siteSettings['crisp'];


$sql = "SELECT `google` FROM `site_settings` WHERE `id`='1';";
$result = mysqli_query($conn, $sql);
$siteSettings = mysqli_fetch_assoc($result);
$googleAnalyticsKey = $siteSettings['google'];




?>



</body>

 <script> 
      document.addEventListener('contextmenu', event=> event.preventDefault()); 
      document.onkeydown = function(e) { 
      if(event.keyCode == 123) { 
      return false; 
      } 
      if(e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)){ 
      return false; 
      } 
      if(e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)){ 
      return false; 
      } 
      if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)){ 
      return false; 
      } 
      } 
      </script> 

</html>