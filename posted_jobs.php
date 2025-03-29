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
    $delete_sql = "DELETE FROM jobs WHERE id = $job_id AND recruiter_id = $recruiter_id";
    mysqli_query($connection, $delete_sql);
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
    <title>KormoGuru - Posted Job Status</title>
</head>
<body>
    <h2>Jobs You've Posted</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <ul>
            <?php while ($job = mysqli_fetch_assoc($result)): ?>
                <li>
                    <strong><?php echo htmlspecialchars($job['title']); ?></strong> at <?php echo htmlspecialchars($job['company']); ?><br>
                    <em>Required Skills:</em> <?php echo htmlspecialchars($job['skills_required']); ?><br>
                    <a href="edit_jobs.php?id=<?php echo $job['id']; ?>">✏️ Edit</a> |
                    <a href="posted_jobs.php?delete=<?php echo $job['id']; ?>" onclick="return confirm('Are you sure you want to delete this job?');">❌ Delete</a>
                </li><br>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>You haven’t posted any jobs yet.</p>
    <?php endif; ?>

    <p><a href="post_jobs.php">➕ Post a New Job</a></p>
    <p><a href="profile.php">Back to Profile</a></p>
</body>
</html>
