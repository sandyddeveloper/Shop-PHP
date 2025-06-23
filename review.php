<?php
require_once('server/db.php');
require_once 'comp/functions.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

$role = 0;
if (isset($_SESSION["loggedin"])) {
    $id = $_SESSION["id"];
    $username = $_SESSION["username"];
    $role = intval(getUserData($id, "role"));
}

$user_id = $_SESSION["id"];

$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
    $star_count = isset($_POST['star_count']) ? (int)$_POST['star_count'] : 0;
    $review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';

    $insert_sql = "INSERT INTO reviews (star_count, order_id, user_id, review, reviewed_at) VALUES (?, ?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iiis", $star_count, $order_id, $user_id, $review_text);

    $check_sql = "SELECT * FROM reviews WHERE order_id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $order_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $errorMessage = "You have already submitted a review for this order.";
    } else {

    if ($insert_stmt->execute()) {
        
        $successMessage = "Review submitted successfully.";

      
        if ($star_count == 5) {
            $settings_sql = "SELECT feedback_credits, credit_after_feedback FROM site_settings WHERE id = 1";
            $settings_result = $conn->query($settings_sql);
            
            if ($settings_row = $settings_result->fetch_assoc()) {
                if ($settings_row['feedback_credits'] == 1) {
                    $credit = $settings_row['credit_after_feedback'];
                    $update_bal_sql = "UPDATE users SET bal = bal + ? WHERE id = ?";
                    $update_bal_stmt = $conn->prepare($update_bal_sql);
                    $update_bal_stmt->bind_param("ii", $credit, $user_id);
                    
                    if ($update_bal_stmt->execute()) {
                        $successMessage = "Review submitted successfully. Balance updated!";
                    } else {
                        $errorMessage = "Failed to update balance.";
                    }
                }
            }
        } 
    } else {
        $errorMessage = "Failed to submit the review.";
    }
 }
}
?>

<!DOCTYPE html>
<html>

<html lang="en-US">
<link type="text/css" rel="stylesheet" id="dark-mode-custom-link">
<link type="text/css" rel="stylesheet" id="dark-mode-general-link">
<style lang="en" type="text/css" id="dark-mode-custom-style"></style>
<style lang="en" type="text/css" id="dark-mode-native-style"></style>
<style lang="en" type="text-css" id="dark-mode-native-sheet"></style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<head>
    <?php include 'comp/header.php'; ?>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <title><?php echo $shop_title; ?> - Category Manager</title>
</head>

<body>
    
    <?php include 'comp/nav.php'; ?>

    <div class="container">
    <section class="faq_hero_section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="hero_txt">
                            <h6>Review</h6>
                            <p>Leave a review</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Display success message if present -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success" id="popupMessage" role="alert">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        <!-- Display error message if present -->
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger" id="popupMessage" role="alert">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>
<br><br><br><br><br><br>
        <form method="post">
            <div class="form-group">
                <label for="star_count" style="color: white;">Star Count:</label>
                <select class="form-control" name="star_count" id="star_count" style=" background-color: #131313;; color: #fff; border: none !imporant">
                    <option value="1">⭐</option>
                    <option value="2">⭐⭐</option>
                    <option value="3">⭐⭐⭐</option>
                    <option value="4">⭐⭐⭐⭐</option>
                    <option value="5">⭐⭐⭐⭐⭐</option>
                </select>
            </div>

            <div class="form-group">
                <label for="review_text" style="color: white;">Review:</label>
                <textarea class="form-control" style=" background-color: #131313;; color: #fff; border: none !imporant" name="review_text" id="review_text" rows="4" cols="50"></textarea>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary">Submit Review</button>
            </div>
        </form>
    </div>

    <?php include 'comp/footer.php'; ?>
</body>
</html>
