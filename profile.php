<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$success_msg = $_SESSION['success_msg'] ?? '';
unset($_SESSION['success_msg']);

$error_skills = $_SESSION['error_skills'] ?? '';
unset($_SESSION['error_skills']);

$sql = "SELECT username, email, role, skills FROM users WHERE id = $user_id";
$result = mysqli_query($connection, $sql);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    $user = [
        'username' => '',
        'email' => '',
        'role' => '',
        'skills' => ''
    ];
}

if (isset($_POST['skills'])) {
    $skills = mysqli_real_escape_string($connection, $_POST['skills']);
    $update_sql = "UPDATE users SET skills = '$skills' WHERE id = $user_id";

    if (isset($_POST['update_skills'])) {
        if (mysqli_query($connection, $update_sql)) {
            $_SESSION['success_msg'] = "Skills updated successfully!";
        } else {
            $_SESSION['error_skills'] = "Failed to update skills. Please try again.";
        }
        header("Location: profile.php");
        exit();
    }

    if (isset($_POST['delete_skills'])) {
        $update_sql = "UPDATE users SET skills = '' WHERE id = $user_id";
        if (mysqli_query($connection, $update_sql)) {
            $_SESSION['success_msg'] = "All skills deleted.";
        } else {
            $_SESSION['error_skills'] = "Failed to delete skills.";
        }
        header("Location: profile.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>KormoGuru - Your Profile</title>
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
    <h2>Your Profile</h2>
    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>

    <?php if ($role === 'Recruiter'): ?>
        <p><a href="posted_jobs.php">Click to view your posted jobs</a></p>
        <h3>Skills you are looking for</h3>
    <?php else: ?>
        <h3>Your skills</h3>
    <?php endif; ?>

    <p><?= !empty($user['skills']) ? nl2br(htmlspecialchars($user['skills'])) : "Not added yet." ?></p>

    <h3>Update Skills</h3>
    <p>📝 Type and press Enter to add skills. Click ❌ to remove.</p>

    <?php if ($success_msg): ?><p style="color:green;"><?= $success_msg ?></p><?php endif; ?>
    <?php if ($error_skills): ?><p style="color:red;"><?= $error_skills ?></p><?php endif; ?>

    <form method="POST">
        <div class="tag-container" id="tag-container">
            <input type="text" id="tag-input" placeholder="Add a skill...">
        </div>
        <input type="hidden" name="skills" id="skills-hidden" value="<?= htmlspecialchars($user['skills']) ?>">
        <br><br>
        <input type="submit" name="update_skills" value="Add New Skills">
        <input type="submit" name="delete_skills" value="Delete All Skills">
    </form>

    <p><a href="index.php">Go to Home</a></p>
    <p><a href="logout.php">Logout</a></p>
    <p><a href="job_status.php">Your Job Status</a></p>

    <script>
    const tagContainer = document.getElementById('tag-container');
    const input = document.getElementById('tag-input');
    const hiddenInput = document.getElementById('skills-hidden');
    let tags = hiddenInput.value ? hiddenInput.value.split(',').map(s => s.trim()) : [];

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
