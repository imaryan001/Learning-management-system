<?php
session_start();
include("db.php");

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'teacher') {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$message = "";

if (isset($_POST['upload'])) {
    $title = $_POST['title'];
    $file = $_FILES['file']['name'];
    $tmp_name = $_FILES['file']['tmp_name'];
    $target_dir = "uploads/";

    // Create folder if not exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . basename($file);

    if (move_uploaded_file($tmp_name, $target_file)) {
        $stmt = $conn->prepare("INSERT INTO courses (title, filename, uploaded_by) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $file, $user['userid']);
        $stmt->execute();
        $message = "✅ File uploaded successfully!";
    } else {
        $message = "❌ Failed to upload file.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Upload Course</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.container {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    padding: 30px;
    border-radius: 15px;
    width: 400px;
    box-shadow: 0 0 20px rgba(0,0,0,0.3);
    animation: slideUp 1s ease;
}
@keyframes slideUp {
    from {transform: translateY(40px); opacity: 0;}
    to {transform: translateY(0); opacity: 1;}
}
h2 { text-align: center; margin-bottom: 20px; }
input, button {
    width: 100%; padding: 10px; margin-top: 10px;
    border-radius: 8px; border: none; outline: none;
}
button {
    background: #00b09b; color: white; font-weight: bold;
    cursor: pointer; transition: 0.3s;
}
button:hover { background: #96c93d; transform: scale(1.05); }
.message { text-align: center; margin-top: 15px; font-weight: bold; color: yellow; }
</style>
</head>
<body>
<div class="container">
    <h2>📤 Upload Course Material</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Course Title" required>
        <input type="file" name="file" required>
        <button type="submit" name="upload">Upload</button>
    </form>
    <div class="message"><?= $message ?></div>
    <a href="dashboard.php" style="display:block;text-align:center;margin-top:20px;color:#00ffff;">⬅ Back to Dashboard</a>
</div>
</body>
</html>
