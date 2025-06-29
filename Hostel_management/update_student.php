<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id      = $_POST['id'];
    $name    = $_POST['name'];
    $email   = $_POST['email'];
    $phone   = $_POST['phone'];
    $duration = $_POST['duration'];

    // Update user table
    $stmt1 = $conn->prepare("UPDATE user SET name = ?, email = ?, phonenumber = ? WHERE ID = ?");
    $stmt1->bind_param("ssss", $name, $email, $phone, $id);
    $stmt1->execute();

    // Update student table
    $stmt2 = $conn->prepare("UPDATE student SET Duration_of_stay = ? WHERE ID = ?");
    $stmt2->bind_param("ss", $duration, $id);
    $stmt2->execute();

    echo "<p style='color:green;'>✅ Student updated successfully!</p>";
    echo "<a href='view_students.php'>Back to Student List</a>";
}
?>
