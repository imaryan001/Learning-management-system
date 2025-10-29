<?php
session_start();
include("db.php");

// Access control
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Approve user
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $conn->query("UPDATE users SET approved=1 WHERE id=$id");
    header("Location: approve_users.php");
    exit;
}

// Disapprove user
if (isset($_GET['disapprove'])) {
    $id = intval($_GET['disapprove']);
    $conn->query("UPDATE users SET approved=0 WHERE id=$id");
    header("Location: approve_users.php");
    exit;
}

// Delete user
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id=$id");
    header("Location: approve_users.php");
    exit;
}

// Edit user
if (isset($_POST['update_user'])) {
    $id = $_POST['user_id'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $approved = $_POST['approved'];
    $conn->query("UPDATE users SET username='$username', role='$role', approved='$approved' WHERE id=$id");
    header("Location: approve_users.php");
    exit;
}

$result = $conn->query("SELECT * FROM users ORDER BY role, id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Manage Users</title>
<style>
    body {
        background: linear-gradient(135deg, #667eea, #764ba2);
        font-family: "Poppins", sans-serif;
        color: white;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 100vh;
        margin: 0;
        padding: 40px 10px;
    }
    h1 {
        animation: fadeIn 1s ease;
        margin-bottom: 20px;
    }
    table {
        border-collapse: collapse;
        width: 90%;
        max-width: 900px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 0 15px rgba(0,0,0,0.4);
        animation: slideUp 1s ease;
    }
    th, td {
        padding: 15px;
        text-align: center;
        border-bottom: 1px solid rgba(255,255,255,0.2);
    }
    th {
        background: rgba(0,0,0,0.3);
    }
    tr:hover {
        background: rgba(255,255,255,0.15);
        transition: 0.3s ease;
    }
    button, a.btn {
        padding: 8px 15px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        color: white;
        text-decoration: none;
        font-weight: bold;
        transition: all 0.3s ease;
        display: inline-block;
    }
    .approve { background: #00b09b; }
    .approve:hover { background: #96c93d; transform: scale(1.05); }
    .disapprove { background: #ff4e50; }
    .disapprove:hover { background: #ff7e5f; transform: scale(1.05); }
    .edit { background: #007bff; }
    .edit:hover { background: #00c6ff; transform: scale(1.05); }
    .delete { background: #d4145a; }
    .delete:hover { background: #fbb03b; transform: scale(1.05); }
    .logout {
        margin-top: 20px;
        background: white;
        color: #764ba2;
        padding: 10px 20px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: bold;
        transition: all 0.3s ease;
    }
    .logout:hover { background: #00b09b; color: white; transform: scale(1.05); }

    .edit-form {
        background: rgba(0,0,0,0.7);
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        display: flex; justify-content: center; align-items: center;
        visibility: hidden; opacity: 0;
        transition: all 0.5s ease;
    }
    .edit-form.active {
        visibility: visible; opacity: 1;
    }
    .edit-box {
        background: white;
        color: black;
        padding: 30px;
        border-radius: 15px;
        width: 350px;
        text-align: center;
        animation: fadeIn 0.5s ease;
    }
    input, select {
        width: 100%;
        padding: 10px;
        margin: 8px 0;
        border-radius: 8px;
        border: 1px solid #ddd;
    }
    @keyframes fadeIn { from {opacity:0;} to {opacity:1;} }
    @keyframes slideUp { from {transform:translateY(30px); opacity:0;} to {transform:translateY(0); opacity:1;} }
</style>

<script>
function openEdit(id, name, role, approved) {
    document.getElementById("editForm").classList.add("active");
    document.getElementById("user_id").value = id;
    document.getElementById("username").value = name;
    document.getElementById("role").value = role;
    document.getElementById("approved").value = approved;
}
function closeEdit() {
    document.getElementById("editForm").classList.remove("active");
}
</script>
</head>

<body>
    <h1>👑 Admin Dashboard - Manage Users</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Approved</th>
            <th>Actions</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['userid']) ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= ucfirst($row['role']) ?></td>
            <td><?= $row['approved'] ? "✅ Approved" : "⏳ Pending" ?></td>
            <td>
                <?php if ($row['role'] != 'admin'): ?>
                    <?php if ($row['approved'] == 0): ?>
                        <a href="?approve=<?= $row['id'] ?>" class="btn approve">Approve</a>
                    <?php else: ?>
                        <a href="?disapprove=<?= $row['id'] ?>" class="btn disapprove">Disapprove</a>
                    <?php endif; ?>
                    <a href="#" class="btn edit" onclick="openEdit('<?= $row['id'] ?>','<?= $row['username'] ?>','<?= $row['role'] ?>','<?= $row['approved'] ?>')">Edit</a>
                    <a href="?delete=<?= $row['id'] ?>" class="btn delete" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                <?php else: ?>
                    👑 Admin
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <a href="dashboard.php" class="logout">⬅ Back to Dashboard</a>
    <a href="logout.php" class="logout">Logout</a>

    <!-- Edit Modal -->
    <div class="edit-form" id="editForm">
        <div class="edit-box">
            <h2>Edit User</h2>
            <form method="POST">
                <input type="hidden" name="user_id" id="user_id">
                <label>Username:</label>
                <input type="text" name="username" id="username" required>
                <label>Role:</label>
                <select name="role" id="role" required>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                    <option value="admin">Admin</option>
                </select>
                <label>Approved:</label>
                <select name="approved" id="approved" required>
                    <option value="1">Approved</option>
                    <option value="0">Not Approved</option>
                </select>
                <button type="submit" name="update_user" class="approve">Save Changes</button>
                <button type="button" onclick="closeEdit()" class="disapprove">Cancel</button>
            </form>
        </div>
    </div>
</body>
</html>
