<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Welcome to KormoGuru</title>
</head>
<body>
<?php if (isset($_SESSION['username'])): ?>
    <h3>Welcome, <?= $_SESSION['username'] ?>!</h3>
    <h3>Your role: <?= $_SESSION['role'] ?>!</h3>

    <?php if ($_SESSION['role'] === 'User'): ?>
        <p><a href="profile.php">👤 Your Profile</a></p>
        <p><a href="view_jobs.php">🔍 Browse Jobs</a></p>
        <p><a href="skill_gap.php">📊 Skill Gap Analysis</a></p>
        <p><a href="logout.php">🚪 Logout</a></p>

    <?php elseif ($_SESSION['role'] === 'Recruiter'): ?>
        <p><a href="profile.php">👤 Your Profile</a></p>
        <p><a href="view_jobs.php">🔍 Browse Jobs</a></p>
        <p><a href="post_jobs.php">➕ Post a Job</a></p>
        <p><a href="logout.php">🚪 Logout</a></p>
    <?php endif; ?>

<?php else: ?>
    <h2>Welcome to KormoGuru</h2>

    <p>Your career starts here.</p>

    <p>KormoGuru is a simple and focused job portal designed to connect skilled individuals with meaningful opportunities. As a job seeker, you can explore job listings, apply to positions, track your application status, and analyze how well your skills match each job through our built-in skill gap analysis tool.</p>

    <p>Recruiters can post job opportunities, define required skills, and manage applications efficiently within the platform.</p>

    <p>Whether you're looking to grow your career or hire the right talent, KormoGuru gives you the tools to move forward with clarity.</p>

    <p><a href="register.php">Register</a> if you're new, or <a href="login.php">Login</a> to continue.</p>
<?php endif; ?>
</body>
</html>
