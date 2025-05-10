<?php
session_start();
include 'config.php';

// Only allow recruiters
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Recruiter') {
    header("Location: index.php");
    exit();
}

$recruiter_id = $_SESSION['user_id'];

// Handle delete action
if (isset($_GET['delete'])) {
    $job_id = intval($_GET['delete']);

    // First delete related applications
    mysqli_query($connection, "DELETE FROM applications WHERE job_id = $job_id");

    // Then delete related job skills
    mysqli_query($connection, "DELETE FROM job_skills WHERE job_id = $job_id");

    // Then delete the job
    mysqli_query($connection, "DELETE FROM jobs WHERE id = $job_id AND recruiter_id = $recruiter_id");

    header("Location: posted_jobs.php");
    exit();
}

// Fetch posted jobs
$sql = "SELECT * FROM jobs WHERE recruiter_id = $recruiter_id ORDER BY posted_on DESC";
$result = mysqli_query($connection, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="css/posted_jobs.css">
</head>
<body>
    <header>
    <div class="logo"></div>
        <title>KormoGuru - Posted Jobs</title>
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
        <nav class="nav-links">
            <a href="index.php" class="<?= ($current_page == 'index.php') ? 'active-link' : '' ?>">Home</a>
            <a href="browse_jobs.php" class="<?= ($current_page == 'browse_jobs.php') ? 'active-link' : '' ?>">Browse Jobs</a>
            <a href="post_jobs.php" class="<?= ($current_page == 'post_jobs.php') ? 'active-link' : '' ?>">Post Jobs</a>
            <a href="posted_jobs.php" class="<?= ($current_page == 'posted_jobs.php') ? 'active-link' : '' ?>">Posted Jobs</a>
            <a href="job_status.php" class="<?= ($current_page == 'job_status.php') ? 'active-link' : '' ?>">Job Status</a>
            <a href="profile.php" class="<?= ($current_page == 'profile.php') ? 'active-link' : '' ?>">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="main-content">
    <h2>Your Posted Jobs</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <ul>
            <?php while ($job = mysqli_fetch_assoc($result)): ?>
                <?php
                // Fetch required skills from job_skills
                $skills_result = mysqli_query($connection, "SELECT skill FROM job_skills WHERE job_id = " . $job['id']);
                $skills = [];
                while ($row = mysqli_fetch_assoc($skills_result)) {
                    $skills[] = htmlspecialchars($row['skill']);
                }
                ?>
                <li>
                    <strong><?= htmlspecialchars($job['title']) ?></strong> at <?= htmlspecialchars($job['company']) ?><br>
                    <em>Required Skills:</em> <?= !empty($skills) ? implode(', ', $skills) : 'Not specified' ?><br>
                    <a href="edit_jobs.php?id=<?= $job['id'] ?>">✏️ Edit</a> |
                    <a href="posted_jobs.php?delete=<?= $job['id'] ?>" onclick="return confirm('Are you sure you want to delete this job?');">❌ Delete</a>
                </li><br>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>You haven’t posted any jobs yet.</p>
    <?php endif; ?>

    <p><a href="post_jobs.php">➕ Post a New Job</a></p>
</body>
</html>
