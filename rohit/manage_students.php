<?php
session_start();
include("db.php");

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'teacher') {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE id=$id AND role='student'");
    header("Location: manage_students.php");
    exit;
}

// Fetch students
$students = $conn->query("SELECT * FROM users WHERE role='student' ORDER BY username ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Students</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        padding: 30px;
        backdrop-filter: blur(10px);
        box-shadow: 0 0 20px rgba(0,0,0,0.3);
        animation: fadeIn 1s ease;
    }
    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(20px);}
        to {opacity: 1; transform: translateY(0);}
    }
    h2 { text-align: center; margin-bottom: 25px; }
    table {
        width: 100%;
        border-collapse: collapse;
        background: rgba(255,255,255,0.15);
    }
    th, td {
        padding: 12px; text-align: center;
        border-bottom: 1px solid rgba(255,255,255,0.2);
    }
    th { background: rgba(0,0,0,0.2); }
    .btn {
        background: #00b09b;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        transition: 0.3s;
        font-weight: bold;
    }
    .btn:hover { transform: scale(1.05); background: #96c93d; }
    .btn-danger {
        background: #ff4d4d;
    }
    .btn-danger:hover {
        background: #e60000;
    }
    .search-box {
        margin-bottom: 20px;
        text-align: center;
    }
    .search-box input {
        padding: 8px 10px;
        border-radius: 8px;
        border: none;
        outline: none;
        width: 250px;
        text-align: center;
    }
    a.back {
        display: block;
        text-align: center;
        margin-top: 20px;
        color: #00ffff;
        text-decoration: none;
        font-weight: bold;
    }
    a.back:hover { text-decoration: underline; }
</style>
<script>
function confirmDelete(id) {
    Swal.fire({
        title: "Are you sure?",
        text: "You are about to remove this student.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete!",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#d33"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "manage_students.php?delete=" + id;
        }
    });
}
function searchTable() {
    let input = document.getElementById('search').value.toLowerCase();
    let rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(input) ? '' : 'none';
    });
}
</script>
</head>
<body>
<div class="container">
    <h2>👨‍🎓 Manage Students</h2>

    <div class="search-box">
        <input type="text" id="search" onkeyup="searchTable()" placeholder="Search student by name or ID...">
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Approved</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $students->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['userid']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['approved'] ? "✅" : "⏳" ?></td>
                <td>
                    <button class="btn" onclick="Swal.fire('Student Info', 'Name: <?= addslashes($row['username']) ?>\nEmail: <?= addslashes($row['email']) ?>', 'info')">View</button>
                    <button class="btn-danger" onclick="confirmDelete(<?= $row['id'] ?>)">Delete</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="back">⬅ Back to Dashboard</a>
</div>
</body>
</html>
