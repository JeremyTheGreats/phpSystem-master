<?php
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = strtoupper(trim($_POST['coupon_name']));
    $desc = $_POST['description'];
    $cost = intval($_POST['point_cost']);
    $max = intval($_POST['max_uses']);
    $tier = $_POST['tier_label'];

    $stmt = $conn->prepare("INSERT INTO coupon_offers (coupon_name, description, point_cost, max_uses, tier_label) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiis", $name, $desc, $cost, $max, $tier);

    if ($stmt->execute()) {
        header("Location: manage_vouchers.php?success=1");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>