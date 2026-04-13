<?php
session_start();
include "../db.php";

// Security: Only allow admins to change account statuses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = intval($_GET['id']);
    $status = mysqli_real_escape_string($conn, $_GET['status']);

    // Valid status list to prevent database errors
    $allowed_statuses = ['active', 'inactive', 'pending'];
    if (in_array($status, $allowed_statuses)) {
        $query = "UPDATE user SET status = '$status' WHERE id = $id";

        if ($conn->query($query)) {
            header("Location: user.php?update=success");
            exit();
        } else {
            die("Database Error: " . $conn->error);
        }
    }
}
header("Location: user.php");
?>