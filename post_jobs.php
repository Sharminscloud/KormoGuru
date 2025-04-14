<?php
session_start();
include 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Recruiter') {
    header('Location: index.php');
    exit();
}

$success_msg = $error_msg = '';
if (isset($_POST['post_jobs'])) {
    $recruiter_id = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($connection, $_POST['title']);
    $company = mysqli_real_escape_string($connection, $_POST['company']);
    $skills_required = mysqli_real_escape_string($connection, $_POST['skills_required']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);

    $sql = "INSERT INTO jobs(recruiter_id, title, company, skills_required, Description) VALUES ('$recruiter_id', '$title', '$company', '$skills_required', '$description')";
    if (mysqli_query($connection, $sql)) {
        $success_msg = "Job posted successfully!";
    } else {
        $error_msg = "Failed to post job:" . mysqli_error($connection);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="css/post_jobs.css">
    <title>KormoGuru - Post a job</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Assuming your styles are here -->
</head>
<body>
    <header>
        <div class="logo"></div>
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        ?>
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

    <h2>Post a new job!</h2>

    <?php if ($success_msg): ?>
        <p class="success"><?= $success_msg ?></p>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <p class="error"><?= $error_msg ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Job Title:</label><br>
        <input type="text" name="title" required><br><br>

        <label>Company name:</label><br>
        <input type="text" name="company" required><br><br>

        <label>Required Skills:</label><br>
        <p>Type and press Enter to add skills. Click ❌ to remove.</p>
        <div class="tag-container" id="tag-container">
            <input type="text" id="tag-input" placeholder="Add a skill... (Press Enter or comma to add)">
        </div>
        <input type="hidden" name="skills_required" id="skills-hidden"><br><br>

        <label>Job description:</label><br>
        <textarea name="description" rows="5" cols="50" placeholder="Describe the job.." required></textarea><br><br>

        <input type="submit" name="post_jobs" value="Click to Post Job">
    </form>

    <script>
    const tagContainer = document.getElementById('tag-container');
    const input = document.getElementById('tag-input');
    const hiddenInput = document.getElementById('skills-hidden');
    let tags = [];

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
        tagContainer.appendChild(input);
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
