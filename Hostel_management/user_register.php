<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
</head>
<body>

<h2>User Registration Form</h2>

<form method="POST" action="register_user.php">
    Name: <input type="text" name="name" required><br><br>
    Email: <input type="email" name="email" required><br><br>
    Phone Number: <input type="text" name="phonenumber" required><br><br>
    Password: <input type="password" name="password" required><br><br>
    
    Role:
    <select name="role" required>
        <option value="student">Student</option>
        <option value="warden">Warden</option>
        <option value="admin">Admin</option>
    </select><br><br>

    <button type="submit">Register</button>
</form>

</body>
</html>
