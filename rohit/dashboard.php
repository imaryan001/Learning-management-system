<?php
session_start();
include("db.php");

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
$user = $_SESSION['user'];

// Check approval before allowing access
$username = $user['username'];
$check = $conn->query("SELECT approval_status FROM users WHERE username='$username'");
$status = $check->fetch_assoc();

if ($status['approval_status'] != 'approved') {
    echo "<script>alert('❌ Your account is not approved yet. Please wait for admin approval.'); window.location='logout.php';</script>";
    exit;
}

$message = "";

// Create folders and tables
if (!is_dir("assignments")) mkdir("assignments");

$conn->query("CREATE TABLE IF NOT EXISTS assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    file_path VARCHAR(255),
    uploaded_by VARCHAR(100),
    uploaded_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100),
    email VARCHAR(100),
    role ENUM('admin','teacher','manager','student'),
    password VARCHAR(255),
    approval_status ENUM('pending','approved','disapproved') DEFAULT 'pending'
)");

// Handle assignment upload
if (isset($_POST['upload_assignment'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $file = $_FILES['file']['name'];
    $uniqueName = time() . "_" . basename($file);
    $target = "assignments/" . $uniqueName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        $conn->query("INSERT INTO assignments (title, description, file_path, uploaded_by)
                      VALUES ('$title', '$desc', '$uniqueName', '{$user['username']}')");
        $message = "✅ Assignment uploaded successfully!";
    } else {
        $message = "❌ Upload failed!";
    }
}

// ✅ Update assignment (with optional file change)
if (isset($_POST['update_assignment'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $desc = $_POST['description'];

    // Check if new file uploaded
    if (!empty($_FILES['file']['name'])) {
        $file = $_FILES['file']['name'];
        $uniqueName = time() . "_" . basename($file);
        $target = "assignments/" . $uniqueName;

        // Get old file to delete
        $oldFile = $conn->query("SELECT file_path FROM assignments WHERE id=$id")->fetch_assoc()['file_path'];
        if (file_exists("assignments/$oldFile")) unlink("assignments/$oldFile");

        if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            $conn->query("UPDATE assignments SET title='$title', description='$desc', file_path='$uniqueName' WHERE id=$id");
            $message = "✅ Assignment updated (file replaced)!";
        } else {
            $message = "❌ File update failed!";
        }
    } else {
        // Update only title and description
        $conn->query("UPDATE assignments SET title='$title', description='$desc' WHERE id=$id");
        $message = "✅ Assignment updated successfully!";
    }
}

// Delete assignment
if (isset($_POST['delete_assignment'])) {
    $id = $_POST['id'];
    $oldFile = $conn->query("SELECT file_path FROM assignments WHERE id=$id")->fetch_assoc()['file_path'];
    if (file_exists("assignments/$oldFile")) unlink("assignments/$oldFile");
    $conn->query("DELETE FROM assignments WHERE id=$id");
    $message = "🗑️ Assignment deleted!";
}

// Handle user approval
if (isset($_POST['approve_user'])) {
    $uid = $_POST['uid'];
    $conn->query("UPDATE users SET approval_status='approved' WHERE id=$uid");
}
if (isset($_POST['disapprove_user'])) {
    $uid = $_POST['uid'];
    $conn->query("UPDATE users SET approval_status='disapproved' WHERE id=$uid");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LMS Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    * {margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
    body {background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;min-height:100vh;text-align:center;}
    header {display:flex;justify-content:space-between;align-items:center;padding:15px 40px;
        background:rgba(255,255,255,0.1);backdrop-filter:blur(15px);box-shadow:0 0 15px rgba(0,0,0,0.3);}
    .logo {font-size:24px;font-weight:600;}
    nav {display:flex;gap:10px;}
    nav a {color:#fff;text-decoration:none;padding:10px 18px;border-radius:25px;transition:0.3s;}
    nav a:hover {background:rgba(255,255,255,0.2);transform:scale(1.05);}
    .logout {background:#ff4d4d;padding:10px 18px;border:none;border-radius:25px;color:white;cursor:pointer;}
    .container {padding:30px;background:rgba(255,255,255,0.1);margin:40px auto;width:90%;max-width:1000px;
        border-radius:20px;backdrop-filter:blur(10px);}
    section {display:none;}
    form input, form textarea {width:100%;padding:10px;border-radius:8px;border:none;margin:5px 0;}
    form button {background:#00b09b;color:white;padding:10px 20px;border:none;border-radius:10px;margin-top:10px;cursor:pointer;}
    form button:hover {background:#96c93d;}
    .assignment-card {background:rgba(255,255,255,0.2);border-radius:10px;padding:15px;margin:10px 0;}
    .assignment-card a {color:#00ffff;text-decoration:none;}
    .user-card {background:rgba(0,0,0,0.2);padding:15px;border-radius:10px;margin:10px 0;}
    .approve {background:#28a745;color:white;border:none;padding:5px 10px;border-radius:8px;cursor:pointer;}
    .disapprove {background:#dc3545;color:white;border:none;padding:5px 10px;border-radius:8px;cursor:pointer;}
</style>
</head>
<body>

<header>
    <div class="logo">📚 SmartLMS</div>
    <nav>
        <a href="#" onclick="show('dashboard')">Dashboard</a>
        <?php if ($user['role']=='admin'): ?>
            <a href="#" onclick="show('manageUsers')">Manage Users</a>
        <?php endif; ?>
        <?php if (in_array($user['role'], ['admin','teacher'])): ?>
            <a href="#" onclick="show('uploadAssign')">Upload Assignment</a>
        <?php endif; ?>
        <a href="#" onclick="show('viewAssign')">View Assignments</a>
        <a href="logout.php" class="logout">Logout</a>
    </nav>
</header>

<div class="container">
    <section id="dashboard" style="display:block;">
        <h2>Welcome, <?= htmlspecialchars($user['username']) ?> 🎉</h2>
        <h3>Role: <?= ucfirst($user['role']) ?></h3>
        <?php if($message) echo "<p style='color:yellow;font-weight:bold;'>$message</p>"; ?>
    </section>

    <?php if ($user['role']=='admin'): ?>
    <section id="manageUsers">
        <h2>👥 Manage Users</h2>
        <?php
        $users = $conn->query("SELECT * FROM users WHERE role!='admin'");
        while($u = $users->fetch_assoc()){
            echo "<div class='user-card'>
                    <strong>{$u['username']}</strong> ({$u['role']}) - {$u['email']}<br>
                    Status: <b style='color:yellow'>{$u['approval_status']}</b><br>
                    <form method='POST' style='margin-top:5px;'>
                        <input type='hidden' name='uid' value='{$u['id']}'>
                        <button name='approve_user' class='approve'>Approve</button>
                        <button name='disapprove_user' class='disapprove'>Disapprove</button>
                    </form>
                  </div>";
        }
        ?>
    </section>
    <?php endif; ?>

    <?php if (in_array($user['role'], ['admin','teacher'])): ?>
    <section id="uploadAssign">
        <h2>📝 Upload Assignment</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Title" required>
            <textarea name="description" placeholder="Description" required></textarea>
            <input type="file" name="file" required>
            <button name="upload_assignment">Upload</button>
        </form>
    </section>
    <?php endif; ?>

    <section id="viewAssign">
        <h2>📘 All Assignments</h2>
        <?php
        $res = $conn->query("SELECT * FROM assignments ORDER BY uploaded_on DESC");
        if ($res->num_rows>0) {
            while($row=$res->fetch_assoc()){
                echo "<div class='assignment-card'>
                        <h4>{$row['title']}</h4>
                        <p>{$row['description']}</p>
                        <small>By {$row['uploaded_by']} on {$row['uploaded_on']}</small><br>
                        <a href='assignments/{$row['file_path']}' download>⬇️ Download</a>";
                if (in_array($user['role'], ['admin','teacher'])) {
                    echo "<form method='POST' enctype='multipart/form-data' style='margin-top:5px;'>
                            <input type='hidden' name='id' value='{$row['id']}'>
                            <input type='text' name='title' value='{$row['title']}' required>
                            <textarea name='description'>{$row['description']}</textarea>
                            <input type='file' name='file'>
                            <small>Leave blank to keep current file.</small><br>
                            <button name='update_assignment'>Update</button>
                            <button name='delete_assignment' style='background:#ff4d4d;'>Delete</button>
                          </form>";
                }
                echo "</div>";
            }
        } else echo "<p>No assignments uploaded yet.</p>";
        ?>
    </section>
</div>

<script>
function show(id){
    document.querySelectorAll("section").forEach(s=>s.style.display='none');
    document.getElementById(id).style.display='block';
}
</script>

</body>
</html>
