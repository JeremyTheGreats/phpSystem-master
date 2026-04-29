<?php

$conn = mysqli_connect("localhost", "root", "", "crimsonDB"); 

if (!$conn) {
    die("Connection Failed!: " . mysqli_connect_error());
}
?>