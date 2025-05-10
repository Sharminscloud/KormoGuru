<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to KormoGuru</title>
    <link rel="stylesheet" type="text/css" href="css/index.css">
</head>
<body>
<header></header>

<div class="overlay">
    <div class="container">

        <?php if (isset($_SESSION['username'])): ?>
            <h2>🚀 Explore KormoGuru — Your career starts here!</h2>
            <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
            <h1>Your role: <?= htmlspecialchars($_SESSION['role']) ?>!</h1>

            <div class="link-group">
                <?php if ($_SESSION['role'] === 'User'): ?>
                    <a href="profile.php">👤 Your Profile</a><br>
                    <a href="browse_jobs.php">🔍 Browse Jobs</a><br>
                    <a href="skill_gap.php">📊 Skill Gap Analysis</a><br>
                    <a href="logout.php">🚪 Logout</a>
                <?php elseif ($_SESSION['role'] === 'Recruiter'): ?>
                    <a href="profile.php">👤 Your Profile</a><br>
                    <a href="browse_jobs.php">🔍 Browse Jobs</a><br>
                    <a href="post_jobs.php">➕ Post Jobs</a><br>
                    <a href="logout.php">🚪 Logout</a>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <h1>Welcome to <span style="color:#FF9800">KormoGuru</span></h1>

            <p>Your career starts here.</p>

            <p>KormoGuru is a simple and focused job portal designed to connect skilled individuals with meaningful opportunities.</p>
            <p>As a job seeker, you can explore job listings, apply to positions, track your application status, and analyze how well your skills match each job through our built-in skill gap analysis tool.</p>

            <p>Recruiters can post job opportunities, define required skills, and manage applications efficiently within the platform.</p>

            <p>Whether you're looking to grow your career or hire the right talent, KormoGuru gives you the tools to move forward with clarity.</p>

            <div class="link-group">
                <a href="register.php">📝 Register</a><br>
                <a href="login.php">🔑 Login</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
