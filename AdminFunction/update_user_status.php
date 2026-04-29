<?php
session_start();
include "../db.php";

// 1. SECURITY CHECK
// Updated 'admin' to 'Admin' to match your session logic
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Unauthorized access.");
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = intval($_GET['id']);
    $status = $_GET['status'];

    // 2. VALIDATION
    // Ensure the incoming status is one of your allowed database values
    $allowed_statuses = ['active', 'inactive', 'pending'];

    if (in_array($status, $allowed_statuses)) {
        // 3. PREPARED STATEMENT
        // Using ? placeholders is much safer than passing variables directly into the string
        $stmt = $conn->prepare("UPDATE user SET status = ? WHERE user_id = ?");
        $stmt->bind_param("si", $status, $id);

        if ($stmt->execute()) {
            // Redirect back to the user management list
            header("Location: user.php?update=success");
            exit();
        } else {
            die("Database Error: " . $conn->error);
        }
    } else {
        // If an invalid status was passed, just go back
        header("Location: user.php?update=invalid_status");
        exit();
    }
}

// Default redirect if parameters are missing
header("Location: user.php");
exit();
?>