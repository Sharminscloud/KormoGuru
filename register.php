<?php
session_start();
include 'config.php';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', '$role')";

    if (mysqli_query($connection, $sql)) {
        $_SESSION['user_id'] = mysqli_insert_id($connection);
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        header("Location: index.php");
        exit();
    } else {
        $error_msg = "❌ Error: " . mysqli_error($connection);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>KormoGuru - Register</title>
</head>
<body>
<h3>Register at KormoGuru</h3>
<form method="POST">
    <label>Username:</label><br>
    <input type="text" name="username" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <label>Role:</label><br>
    <select name="role">
        <option value="User">User</option>
        <option value="Recruiter">Recruiter</option>
    </select><br><br>

    <input type="submit" name="register" value="Register">
</form>

<?php if (isset($error_msg)) echo "<p style='color:red;'>$error_msg</p>"; ?>
<p><a href="index.php">Back to Home</a></p>
</body>
</html>

