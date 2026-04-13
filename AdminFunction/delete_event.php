<?php
include '../db.php';
session_start();

// Security Check
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Optional: Fetch the image path first to delete the file from the folder
    $res = mysqli_query($conn, "SELECT poster FROM events WHERE id = $id");
    $event = mysqli_fetch_assoc($res);

    if ($event) {
        // Delete the physical image file if it exists
        if (file_exists("../" . $event['poster'])) {
            unlink("../" . $event['poster']);
        }

        // Delete from database
        $sql = "DELETE FROM events WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("Location: admindash.php?msg=deleted");
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }
} else {
    header("Location: admindash.php");
}
?>