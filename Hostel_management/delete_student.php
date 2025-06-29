<?php
include 'db_connect.php';

// Check if ID is passed
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // First delete from student table (child table)
    $conn->query("DELETE FROM student WHERE ID = $id");

    // Then delete from user table (parent table)
    $conn->query("DELETE FROM user WHERE ID = $id");

    echo "<p style='color:green;'>✅ Student deleted successfully!</p>";
    echo "<a href='view_students.php'>Back to Student List</a>";
} else {
    echo "<p style='color:red;'>❌ No student ID provided to delete.</p>";
}
?>
