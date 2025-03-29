<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// === Recruiter updates status ===
if ($role === 'Recruiter' && isset($_POST['update_status'])) {
    $app_id = intval($_POST['app_id']);
    $new_status = mysqli_real_escape_string($connection, $_POST['status']);

    // Optional: Ensure this recruiter owns the job
    $check_sql = "SELECT a.id FROM applications a
                  JOIN jobs j ON a.job_id = j.id
                  WHERE a.id = $app_id AND j.recruiter_id = $user_id";
    $check_result = mysqli_query($connection, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $update_sql = "UPDATE applications SET status = '$new_status' WHERE id = $app_id";
        mysqli_query($connection, $update_sql);
    }
}

// === Queries for each role ===
if ($role === 'User') {
    $sql = "SELECT a.id AS app_id, j.title, j.company, a.status, a.applied_on
            FROM applications a
            JOIN jobs j ON a.job_id = j.id
            WHERE a.user_id = $user_id
            ORDER BY a.applied_on DESC";
} else if ($role === 'Recruiter') {
    $sql = "SELECT a.id AS app_id, j.title, j.company, u.username AS applicant, a.status, a.applied_on
            FROM applications a
            JOIN jobs j ON a.job_id = j.id
            JOIN users u ON a.user_id = u.id
            WHERE j.recruiter_id = $user_id
            ORDER BY a.applied_on DESC";
}

$result = mysqli_query($connection, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Job Status - KormoGuru</title>
</head>
<body>
    <h2>Job Application Status</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <ul>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <li>
                    <strong><?php echo htmlspecialchars($row['title']); ?></strong> at <?php echo htmlspecialchars($row['company']); ?><br>

                    <?php if ($role === 'User'): ?>
                        <em>Status:</em> <?php echo htmlspecialchars($row['status']); ?><br>
                        <em>Applied On:</em> <?php echo $row['applied_on']; ?>

                    <?php elseif ($role === 'Recruiter'): ?>
                        <em>Applicant:</em> <?php echo htmlspecialchars($row['applicant']); ?><br>
                        <form method="POST" style="margin-top: 5px;">
                            <input type="hidden" name="app_id" value="<?php echo $row['app_id']; ?>">
                            <label>Status:</label>
                            <select name="status">
                                <option value="Under Review" <?php if ($row['status'] === 'Under Review') echo "selected"; ?>>Under Review</option>
                                <option value="Shortlisted" <?php if ($row['status'] === 'Shortlisted') echo "selected"; ?>>Shortlisted</option>
                                <option value="Hired" <?php if ($row['status'] === 'Hired') echo "selected"; ?>>Hired</option>
                                <option value="Rejected" <?php if ($row['status'] === 'Rejected') echo "selected"; ?>>Rejected</option>
                            </select>
                            <input type="submit" name="update_status" value="Update">
                        </form>
                        <em>Received On:</em> <?php echo $row['applied_on']; ?>
                    <?php endif; ?>
                </li><br>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No applications found.</p>
    <?php endif; ?>

    <p><a href="profile.php">Go to My Profile</a></p>
</body>
</html>


