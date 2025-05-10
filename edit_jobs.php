<?php
session_start();
include 'config.php';

// Only recruiters can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Recruiter') {
    header("Location: index.php");
    exit();
}

$recruiter_id = $_SESSION['user_id'];
$job_id = intval($_GET['id']);

// Fetch job to edit
$sql = "SELECT * FROM jobs WHERE id = $job_id AND recruiter_id = $recruiter_id";
$result = mysqli_query($connection, $sql);
$job = mysqli_fetch_assoc($result);

if (!$job) {
    echo "<p>❌ Job not found or you're not authorized to edit this.</p>";
    exit();
}

// Handle form submission
if (isset($_POST['update_job'])) {
    $title = mysqli_real_escape_string($connection, $_POST['title']);
    $company = mysqli_real_escape_string($connection, $_POST['company']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $skills_array = array_filter(array_map('trim', explode(',', $_POST['skills_required'])));

    $update_sql = "UPDATE jobs 
                   SET title='$title', company='$company', description='$description'
                   WHERE id=$job_id AND recruiter_id=$recruiter_id";

    if (mysqli_query($connection, $update_sql)) {
        // Delete old skills and insert new ones
        mysqli_query($connection, "DELETE FROM job_skills WHERE job_id = $job_id");
        foreach ($skills_array as $skill) {
            $skill = strtolower(mysqli_real_escape_string($connection, $skill));
            mysqli_query($connection, "INSERT INTO job_skills (job_id, skill) VALUES ($job_id, '$skill')");
        }

        header("Location: posted_jobs.php");
        exit();
    } else {
        echo "<p>❌ Update failed: " . mysqli_error($connection) . "</p>";
    }
}

// Fetch skills from job_skills table
$skill_list = [];
$skill_result = mysqli_query($connection, "SELECT skill FROM job_skills WHERE job_id = $job_id");
while ($row = mysqli_fetch_assoc($skill_result)) {
    $skill_list[] = $row['skill'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>KormoGuru - Edit Job</title>
    <link rel="stylesheet" href="css/edit_jobs.css">
</head>
<body>
<header>
    <div class="logo"></div>
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
<h2>Edit Your Job</h2>
<form method="POST">
    <label>Job Title:</label><br>
    <input type="text" name="title" value="<?= htmlspecialchars($job['title']) ?>" required><br><br>

    <label>Company Name:</label><br>
    <input type="text" name="company" value="<?= htmlspecialchars($job['company']) ?>" required><br><br>

    <label>Required Skills:</label><br>
    <p>Type and press Enter to add skills. Click ❌ to remove.</p>
    <div class="tag-container" id="tag-container">
        <input type="text" id="tag-input" placeholder="Add a skill...">
    </div>
    <input type="hidden" name="skills_required" id="skills-hidden"><br><br>

    <label>Job Description:</label><br>
    <textarea name="description" rows="5" cols="50" required><?= htmlspecialchars($job['description']) ?></textarea><br><br>

    <input type="submit" name="update_job" value="Update Job">
</form>

<script>
const tagContainer = document.getElementById('tag-container');
const input = document.getElementById('tag-input');
const hiddenInput = document.getElementById('skills-hidden');
let tags = <?= json_encode($skill_list) ?>;

function createTag(text) {
    const tag = document.createElement('span');
    tag.classList.add('tag');
    tag.innerText = text;

    const closeBtn = document.createElement('span');
    closeBtn.innerText = '❌';
    closeBtn.classList.add('remove-tag');
    closeBtn.onclick = function () {
        tags = tags.filter(t => t !== text);
        updateTags();
    };

    tag.appendChild(closeBtn);
    return tag;
}

function updateTags() {
    tagContainer.innerHTML = '';
    tags.forEach(tag => tagContainer.appendChild(createTag(tag)));
    if (!tagContainer.contains(input)) {
        tagContainer.appendChild(input);
    }
    hiddenInput.value = tags.join(',');
}

input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        const value = input.value.trim().replace(/,+$/, '');
        if (value && !tags.includes(value)) {
            tags.push(value);
            input.value = '';
            updateTags();
        }
    }
});

updateTags();
</script>
</body>
</html>
