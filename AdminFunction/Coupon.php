<?php
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['coupon_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $cost = intval($_POST['point_cost']);
    $max = intval($_POST['max_uses']);
    $tier = mysqli_real_escape_string($conn, $_POST['tier_label']);

    // INSERT INTO 'rewards_coupon' instead of 'coupon_offers'
    $query = "INSERT INTO rewards_coupon (coupon_name, description, point_cost, max_uses, tier_label) 
              VALUES ('$name', '$desc', '$cost', '$max', '$tier')";

    if ($conn->query($query)) {
        header("Location: manage_vouchers.php?success=1");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>