<?php
session_start();
include("db.php");

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$result = $conn->query("SELECT * FROM courses ORDER BY uploaded_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Courses</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 40px;
}
.container {
    background: rgba(255,255,255,0.1);
    border-radius: 15px;
    backdrop-filter: blur(10px);
    padding: 30px;
    box-shadow: 0 0 20px rgba(0,0,0,0.3);
}
h2 { text-align: center; margin-bottom: 20px; }
table {
    width: 100%; border-collapse: collapse;
    background: rgba(255,255,255,0.2);
}
th, td {
    padding: 12px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.2);
}
a.download {
    color: #00ffff; text-decoration: none; font-weight: bold;
}
a.download:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="container">
    <h2>📘 Available Courses</h2>
    <table>
        <tr>
            <th>Title</th>
            <th>Uploaded By</th>
            <th>Date</th>
            <th>Download</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['uploaded_by']) ?></td>
            <td><?= $row['uploaded_at'] ?></td>
            <td><a class="download" href="uploads/<?= $row['filename'] ?>" download>⬇ Download</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <a href="dashboard.php" style="display:block;text-align:center;margin-top:20px;color:#00ffff;">⬅ Back to Dashboard</a>
</div>
</body>
</html>
