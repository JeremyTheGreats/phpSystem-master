<?php
include '../db.php';
session_start();

// 1. SECURITY CHECK
// Updated 'admin' to 'Admin' to match your session logic in manage_vouchers.php
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // 2. FETCH POSTER PATH
    // Updated table name to 'event' (singular) as seen in your database screenshot
    $res = mysqli_query($conn, "SELECT poster FROM events WHERE id = $id");
    $event = mysqli_fetch_assoc($res);

    if ($event) {
        // Delete the physical image file from the server folder if it exists
        if (!empty($event['poster']) && file_exists("../" . $event['poster'])) {
            unlink("../" . $event['poster']);
        }

        // 3. DATABASE DELETION
        // Using Prepared Statements for security
        $sql = "DELETE FROM events WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // Redirect back to the event console with a success message
            header("Location: event.php?msg=deleted");
            exit;
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    } else {
        // Event not found in database
        header("Location: event.php?error=notfound");
        exit;
    }
} else {
    // No ID provided
    header("Location: event.php");
    exit;
}
?>