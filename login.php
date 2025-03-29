<?php
session_start();
include 'config.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($connection, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php");
        exit();
    } else {
        $error_msg = "❌ Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>KormoGuru - Login</title>
</head>
<body>
<h3>Login to KormoGuru</h3>
<form method="POST">
    <label>Username:</label><br>
    <input type="text" name="username" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <input type="submit" name="login" value="Login">
</form>

<?php if (isset($error_msg)) echo "<p style='color:red;'>$error_msg</p>"; ?>
<p><a href="index.php">Back to Home</a></p>
</body>
</html>
