<?php
session_start();
include '../db.php';

// Check if Admin
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Use the correct table name from your DB: rewards_coupon
    $query = "DELETE FROM rewards_coupon WHERE coupon_id = $id";

    if ($conn->query($query)) {
        header("Location: manage_vouchers.php?deleted=1");
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    header("Location: manage_vouchers.php");
}
?>