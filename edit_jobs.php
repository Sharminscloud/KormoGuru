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
    $skills_required = mysqli_real_escape_string($connection, $_POST['skills_required']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);

    $update_sql = "UPDATE jobs 
                   SET title='$title', company='$company', skills_required='$skills_required', description='$description'
                   WHERE id=$job_id AND recruiter_id=$recruiter_id";

    if (mysqli_query($connection, $update_sql)) {
        header("Location: posted_jobs.php");
        exit();
    } else {
        echo "<p>❌ Update failed: " . mysqli_error($connection) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>KormoGuru - Edit Job</title>
    <style>
        .tag-container {
            border: 1px solid #ccc;
            padding: 5px;
            display: flex;
            flex-wrap: wrap;
            min-height: 40px;
            width: 300px;
            background: #fff;
        }
        .tag-container input {
            border: none;
            outline: none;
            flex: 1;
            padding: 5px;
        }
        .tag {
            background: #eee;
            padding: 5px 10px;
            margin: 2px;
            border-radius: 3px;
        }
        .tag .remove-tag {
            margin-left: 8px;
            color: red;
            cursor: pointer;
        }
    </style>
</head>
<body>
<h2>Edit Job Posting</h2>

<form method="POST">
    <label>Job Title:</label><br>
    <input type="text" name="title" value="<?= htmlspecialchars($job['title']) ?>" required><br><br>

    <label>Company Name:</label><br>
    <input type="text" name="company" value="<?= htmlspecialchars($job['company']) ?>" required><br><br>

    <label>Required Skills:</label><br>
    <div class="tag-container" id="tag-container">
        <input type="text" id="tag-input" placeholder="Add a skill...">
    </div>
    <input type="hidden" name="skills_required" id="skills-hidden"><br><br>

    <label>Job Description:</label><br>
    <textarea name="description" rows="5" cols="50" required><?= htmlspecialchars($job['description']) ?></textarea><br><br>

    <input type="submit" name="update_job" value="Update Job">
</form>
<p><a href="posted_jobs.php">Click to view Posted Jobs</a></p>

<script>
const tagContainer = document.getElementById('tag-container');
const input = document.getElementById('tag-input');
const hiddenInput = document.getElementById('skills-hidden');
let tags = <?= json_encode(array_map('trim', explode(',', $job['skills_required']))) ?>;

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
