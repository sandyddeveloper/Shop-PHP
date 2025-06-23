<?php



require('server/db.php');


if (isset($_GET['country_id'])) {
    $countryId = $_GET['country_id'];
    $countrySVG = getCountrySVG($conn, $countryId);
    echo $countrySVG;
} else {
    echo 'Invalid request';
}

// Function to get SVG by country ID
function getCountrySVG($conn, $countryId) {
    $sql = "SELECT country_svg FROM country WHERE id = $countryId";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['country_svg'];
}
?>
