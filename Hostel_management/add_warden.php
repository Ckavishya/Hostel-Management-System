<!DOCTYPE html>
<html>
<head>
    <title>Add Warden</title>
</head>
<body>
    <h2>Warden Registration</h2>
    <form method="POST" action="add_warden.php">
        Name: <input type="text" name="name" required><br><br>
        Email: <input type="email" name="email" required><br><br>
        Phone: <input type="text" name="phone" required><br><br>
        Warden Type:
        <select name="type" required>
            <option value="hostel">Hostel</option>
            <option value="discipline">Discipline</option>
            <option value="academic">Academic</option>
        </select><br><br>
        <button type="submit">Add Warden</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        include 'db_connect.php';

        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $type = $_POST['type'];

        // Step 1: Insert into 'user' table
        $conn->query("INSERT INTO user (name, email, phonenumber) VALUES ('$name', '$email', '$phone')");
        $id = $conn->insert_id;

        // Step 2: Insert into 'warden' table
        $conn->query("INSERT INTO warden (ID, type) VALUES ($id, '$type')");

        echo "<p style='color:green;'>✅ Warden added successfully!</p>";
    }
    ?>
</body>
</html>
