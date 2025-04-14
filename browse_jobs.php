<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$success_msg = $error_msg = "";
$search = "";

// === If User: fetch skills + applied jobs ===
$user_skills = [];
$applied_jobs = [];

if ($role === 'User') {
    $user_skill_result = mysqli_query($connection, "SELECT skills FROM users WHERE id = $user_id");
    $user_data = mysqli_fetch_assoc($user_skill_result);
    $user_skills_raw = isset($user_data['skills']) ? $user_data['skills'] : '';
    $user_skills = array_filter(array_map('strtolower', array_map('trim', explode(',', $user_skills_raw))));

    $app_sql = "SELECT job_id FROM applications WHERE user_id = $user_id";
    $app_result = mysqli_query($connection, $app_sql);
    while ($row = mysqli_fetch_assoc($app_result)) {
        $applied_jobs[] = $row['job_id'];
    }
}

// === Handle job application ===
if ($role === 'User' && isset($_GET['apply'])) {
    $job_id = intval($_GET['apply']);

    if (in_array($job_id, $applied_jobs)) {
        $error_msg = "❌ You have already applied for this job.";
    } else {
        $apply_sql = "INSERT INTO applications (user_id, job_id) VALUES ($user_id, $job_id)";
        if (mysqli_query($connection, $apply_sql)) {
            $success_msg = "✅ Application submitted successfully!";
            $applied_jobs[] = $job_id;
        } else {
            $error_msg = "❌ Failed to apply: " . mysqli_error($connection);
        }
    }
}

// === Handle Search ===
$where_clause = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($connection, $_GET['search']);
    $where_clause = "WHERE j.title LIKE '%$search%' OR j.company LIKE '%$search%' OR j.skills_required LIKE '%$search%'";
}

// === Fetch jobs + recruiter info ===
$jobs_sql = "SELECT j.*, u.username AS recruiter_name, u.email AS recruiter_email
             FROM jobs j
             JOIN users u ON j.recruiter_id = u.id
             $where_clause
             ORDER BY j.posted_on DESC";
$jobs_result = mysqli_query($connection, $jobs_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>KormoGuru - Browse Jobs</title>
    <link rel="stylesheet" type="text/css" href="css/browse_jobs.css">
</head>
<body>

<!-- ✅ Updated Header + Navigation -->
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

<!-- Main Content -->
<main class="main-content">
    <h2>All Job Listings</h2>

    <!-- Search Form -->
    <form method="GET">
        <input type="text" name="search" placeholder="Search by title, company, or skill..." value="<?php echo htmlspecialchars($search); ?>">
        <input type="submit" value="Search">
    </form>

    <!-- Messages -->
    <?php if ($success_msg) echo "<p style='color: green;'>$success_msg</p>"; ?>
    <?php if ($error_msg) echo "<p style='color: red;'>$error_msg</p>"; ?>

    <!-- Job Listings -->
    <?php if (mysqli_num_rows($jobs_result) > 0): ?>
        <ul>
            <?php while ($job = mysqli_fetch_assoc($jobs_result)): ?>
                <li>
                    <strong><?php echo htmlspecialchars($job['title']); ?></strong> at <?php echo htmlspecialchars($job['company']); ?><br>
                    <em>Required Skills:</em> <?php echo htmlspecialchars($job['skills_required']); ?><br>
                    <em>Description:</em> <?php echo htmlspecialchars($job['description']); ?><br>
                    <em>Posted By:</em> <?php echo htmlspecialchars($job['recruiter_name']); ?> 
                    (<a href="mailto:<?php echo htmlspecialchars($job['recruiter_email']); ?>">Contact</a>)<br>

                    <?php if ($role === 'User'): ?>


                        <?php if (in_array($job['id'], $applied_jobs)): ?>
                            <span style="color: green;">✅ Already Applied</span>
                        <?php else: ?>
                            <a href="browse_jobs.php?apply=<?php echo $job['id']; ?>&search=<?php echo urlencode($search); ?>">📩 Apply Now</a>
                        <?php endif; ?>

                    <?php elseif ($role === 'Recruiter' && $job['recruiter_id'] == $user_id): ?>
                        <a href="edit_jobs.php?id=<?php echo $job['id']; ?>">✏️ Edit Job</a>
                    <?php endif; ?>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No jobs found for your search.</p>
    <?php endif; ?>
</main>

</body>
</html>
