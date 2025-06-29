<!DOCTYPE html>
<html>
<head>
    <title>View Wardens</title>
</head>
<body>
    <h2>List of Wardens</h2>

    <?php
    include 'db_connect.php';

    $sql = "SELECT user.ID, user.name, user.email, user.phonenumber
            FROM user
            JOIN warden ON user.ID = warden.ID
			WHERE user.role = 'warden'";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Actions</th>
              </tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['ID']}</td>";
            echo "<td>{$row['name']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "<td>{$row['phonenumber']}</td>";
            echo "<td>
                    <a href='edit_warden.php?id={$row['ID']}'>Edit</a> |
                    <a href='delete_warden.php?id={$row['ID']}' onclick=\"return confirm('Are you sure you want to delete this warden?');\">Delete</a>
                  </td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<p>No wardens found.</p>";
    }
    ?>
</body>
</html>
