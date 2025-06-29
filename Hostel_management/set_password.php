<?php
include 'db_connect.php';

// Example: Update password for user with ID 1
$user_id = 5;
$plain_password = 'David145@'; // change this for each user
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

$sql = "UPDATE user SET password = '$hashed_password' WHERE ID = $user_id";

if ($conn->query($sql) === TRUE) {
    echo "✅ Password updated successfully for user ID $user_id";
} else {
    echo "❌ Error: " . $conn->error;
}

$conn->close();
?>
