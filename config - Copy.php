$host = "localhost";
$user = "Sharmin Akter";
$password = "sharminsfirstproject";
$dbname = "kormoguru";
<?php
// config.php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "kormoguru";

$connection = mysqli_connect($host, $user, $password, $dbname);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
