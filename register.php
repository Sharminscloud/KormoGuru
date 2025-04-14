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
    <header class="logo-header"></header>
    <title>KormoGuru - Register</title>
    <link rel="stylesheet" type="text/css" href="css/login_registration.css">
</head>
<body>
    <div class="auth-container">
        <h2>Register at KormoGuru</h2>
        <form method="POST">
            <label>Username:</label>
            <input type="text" name="username" required>

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <label>Role:</label>
            <select name="role">
                <option value="User">User</option>
                <option value="Recruiter">Recruiter</option>
            </select>

            <input type="submit" name="register" value="Register">
        </form>

        <?php if (isset($error_msg)) echo "<p style='color:red;'>$error_msg</p>"; ?>
        <p><a href="index.php">Back to Home</a></p>
    </div>
</body>
</html>
