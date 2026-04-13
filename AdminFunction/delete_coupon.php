<?php
session_start();
include '../db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Prepare delete statement
    $stmt = $conn->prepare("DELETE FROM coupon_offers WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: manage_vouchers.php?msg=Offer+Deleted+Successfully");
    } else {
        header("Location: manage_vouchers.php?msg=Error+Deleting+Offer");
    }
    $stmt->close();
} else {
    header("Location: manage_vouchers.php");
}
exit();
?>