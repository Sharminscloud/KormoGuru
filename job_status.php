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
    <link rel="stylesheet" type="text/css" href="css/job_status.css">
    <title>KormoGuru - Job Status</title>
</head>
<body>
<header>
    <div class="logo"></div>
    <?php
    $current_page = basename($_SERVER['PHP_SELF']); // Gets the current page filename
    ?>
    <nav class="nav-links">
    <a href="index.php" class="<?= ($current_page == 'index.php') ? 'active-link' : '' ?>">Home</a>
    <a href="browse_jobs.php" class="<?= ($current_page == 'browse_jobs.php') ? 'active-link' : '' ?>">Browse Jobs</a>

    <?php if ($role === 'User'): ?>
        <a href="job_status.php" class="<?= ($current_page == 'job_status.php') ? 'active-link' : '' ?>">Job Status</a>
        <a href="skill_gap.php" class="<?= ($current_page == 'skill_gap.php') ? 'active-link' : '' ?>">Skill Gap</a>
        <a href="profile.php" class="<?= ($current_page == 'profile.php') ? 'active-link' : '' ?>">Profile</a>
        <a href="logout.php">Logout</a>
    <?php elseif ($role === 'Recruiter'): ?>
        <a href="post_jobs.php" class="<?= ($current_page == 'post_jobs.php') ? 'active-link' : '' ?>">Post Jobs</a>
        <a href="posted_jobs.php" class="<?= ($current_page == 'posted_jobs.php') ? 'active-link' : '' ?>">Posted Jobs</a>
        <a href="job_status.php" class="<?= ($current_page == 'job_status.php') ? 'active-link' : '' ?>">Job Status</a>
        <a href="profile.php" class="<?= ($current_page == 'profile.php') ? 'active-link' : '' ?>">Profile</a>
        <a href="logout.php">Logout</a>
    <?php endif; ?>
</nav>
</header>

    <div class="main-content">
        <h2>Job Application Status</h2>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <ul>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <li>
                        <strong><?= htmlspecialchars($row['title']) ?></strong> at <?= htmlspecialchars($row['company']) ?>

                        <?php if ($role === 'User'): ?>
                            <em>Status:</em> <?= htmlspecialchars($row['status']) ?>
                            <em>Applied On:</em> <?= $row['applied_on'] ?>

                        <?php elseif ($role === 'Recruiter'): ?>
                            <em>Applicant:</em> <?= htmlspecialchars($row['applicant']) ?>
                            <form method="POST">
                                <input type="hidden" name="app_id" value="<?= $row['app_id'] ?>">
                                <label>Status:</label>
                                <select name="status">
                                    <option value="Under Review" <?= ($row['status'] === 'Under Review') ? 'selected' : '' ?>>Under Review</option>
                                    <option value="Shortlisted" <?= ($row['status'] === 'Shortlisted') ? 'selected' : '' ?>>Shortlisted</option>
                                    <option value="Hired" <?= ($row['status'] === 'Hired') ? 'selected' : '' ?>>Hired</option>
                                    <option value="Rejected" <?= ($row['status'] === 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                                </select>
                                <input type="submit" name="update_status" value="Update">
                            </form>
                            <em>Received On:</em> <?= $row['applied_on'] ?>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>There are no applications for your jobs at the moment.</p>
        <?php endif; ?>

    </div>
</body>
</html>
