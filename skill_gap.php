<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'User') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = $error_msg = "";

// Fetch user's skills
$user_skill_result = mysqli_query($connection, "SELECT skills FROM users WHERE id = $user_id");
$user_data = mysqli_fetch_assoc($user_skill_result);
$user_skills = array_map('strtolower', array_map('trim', explode(',', $user_data['skills'])));

// Track already applied jobs
$applied = [];
$app_check = mysqli_query($connection, "SELECT job_id FROM applications WHERE user_id = $user_id");
while ($row = mysqli_fetch_assoc($app_check)) {
    $applied[] = $row['job_id'];
}

// Handle application
if (isset($_GET['apply'])) {
    $job_id = intval($_GET['apply']);

    if (in_array($job_id, $applied)) {
        $error_msg = "❌ You have already applied for this job.";
    } else {
        $apply_sql = "INSERT INTO applications (user_id, job_id) VALUES ($user_id, $job_id)";
        if (mysqli_query($connection, $apply_sql)) {
            $success_msg = "✅ Application submitted successfully!";
            $applied[] = $job_id; // update in runtime
        } else {
            $error_msg = "❌ Failed to apply: " . mysqli_error($connection);
        }
    }
}

// Fetch jobs + recruiter info
$jobs_sql = "SELECT j.*, u.username AS recruiter_name, u.email AS recruiter_email
             FROM jobs j
             JOIN users u ON j.recruiter_id = u.id
             ORDER BY j.posted_on DESC";
$jobs_result = mysqli_query($connection, $jobs_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>KormoGuru- Skill Gap</title>
</head>
<body>
    <h2>Available Job Listings</h2>

    <?php if ($success_msg) echo "<p style='color: green;'>$success_msg</p>"; ?>
    <?php if ($error_msg) echo "<p style='color: red;'>$error_msg</p>"; ?>

    <?php if (mysqli_num_rows($jobs_result) > 0): ?>
        <ul>
            <?php while ($job = mysqli_fetch_assoc($jobs_result)): ?>
                <?php
                $required_skills = array_map('strtolower', array_map('trim', explode(',', $job['skills_required'])));
                $matched_skills = array_intersect($user_skills, $required_skills);
                $match_percent = count($required_skills) > 0 ? round((count($matched_skills) / count($required_skills)) * 100) : 0;
                ?>

                <li>
                    <strong><?= htmlspecialchars($job['title']) ?></strong> at <?= htmlspecialchars($job['company']) ?><br>
                    <em>Required Skills:</em> <?= htmlspecialchars($job['skills_required']) ?><br>
                    <em>Description:</em> <?= htmlspecialchars($job['description']) ?><br>
                    <em>Posted By:</em> <?= htmlspecialchars($job['recruiter_name']) ?> 
                    (<a href="mailto:<?= htmlspecialchars($job['recruiter_email']) ?>">Contact</a>)<br>
                    <em>🎯 Skill Match:</em> <?= $match_percent ?>%<br>

                    <?php if (in_array($job['id'], $applied)): ?>
                        <span style="color: green;">✅ Already Applied</span>
                    <?php else: ?>
                        <a href="?apply=<?= $job['id'] ?>">📩 Apply Now</a>
                    <?php endif; ?>
                </li><br>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No jobs posted yet.</p>
    <?php endif; ?>

    <p><a href="profile.php">Back to My Profile</a></p>
</body>
</html>
