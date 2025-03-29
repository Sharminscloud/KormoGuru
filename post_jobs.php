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
    <title>KormoGuru - Post a job</title>
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
    <h2>Post a new job!</h2>
    <?php
    if ($success_msg) echo "<p style='color:green;'>$success_msg</p>";
    if ($error_msg) echo "<p style='color:red;'>$error_msg</p>";
    ?>

    <form method="POST">
        <label>Job Title:</label><br>
        <input type="text" name="title" required><br><br>

        <label>Company name:</label><br>
        <input type="text" name="company" required><br><br>

        <label>Required Skills:</label><br>
        <div class="tag-container" id="tag-container">
            <input type="text" id="tag-input" placeholder="Add a skill...">
        </div>
        <input type="hidden" name="skills_required" id="skills-hidden"><br><br>

        <label>Job description:</label><br>
        <textarea name="description" rows="5" cols="50" placeholder="Describe the job.." required></textarea><br><br>

        <input type="submit" name="post_jobs" value="Click to Post Job">
    </form>

    <p><a href="profile.php">Go to My Profile</a></p>
    <p><a href="index.php">Go to Home</a></p>

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
