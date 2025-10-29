<?php
session_start();
include("db.php");

$message = "";

// =========================== REGISTER ===========================
if (isset($_POST['register'])) {
    $userid = trim($_POST['userid']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));
    $role = $_POST['role'];

    // Empty field check
    if (empty($userid) || empty($username) || empty($email) || empty($password) || empty($role)) {
        $message = "⚠️ All fields are required!";
    } else {
        // Check duplicate UserID or Email
        $check = $conn->query("SELECT * FROM users WHERE userid='$userid' OR email='$email'");
        if ($check->num_rows > 0) {
            $message = "❌ User ID or Email already exists!";
        } else {
            $approved = ($role == 'admin') ? 1 : 0;
            $conn->query("INSERT INTO users (userid, username, email, password, role, approved)
                          VALUES ('$userid', '$username', '$email', '$password', '$role', '$approved')");
            $message = ($role == 'admin')
                ? "✅ Admin registered successfully! You can login now."
                : "✅ Registration successful! Please wait for admin approval.";
        }
    }
}

// =========================== LOGIN ===========================
if (isset($_POST['login'])) {
    $userid = trim($_POST['userid']);
    $password = md5(trim($_POST['password']));

    $result = $conn->query("SELECT * FROM users WHERE userid='$userid' AND password='$password'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($user['role'] == 'admin' || $user['approved'] == 1) {
            $_SESSION['user'] = $user;
            header("Location: dashboard.php");
            exit;
        } else {
            $message = "⚠️ Your account is awaiting admin approval.";
        }
    } else {
        $message = "❌ Invalid User ID or Password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Animated Login & Register</title>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Poppins", sans-serif; }
    body {
        height: 100vh;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex; align-items: center; justify-content: center;
        overflow: hidden;
    }
    .container {
        width: 400px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 20px;
        backdrop-filter: blur(15px);
        box-shadow: 0 0 25px rgba(0,0,0,0.3);
        padding: 30px;
        text-align: center;
        animation: slideUp 1.2s ease;
    }
    @keyframes slideUp {
        from { transform: translateY(60px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    h2 {
        color: #fff;
        font-size: 26px;
        margin-bottom: 15px;
        letter-spacing: 1px;
    }
    form {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    input, select {
        width: 85%;
        padding: 10px;
        margin: 10px 0;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.3s ease;
    }
    input:focus, select:focus {
        outline: none;
        transform: scale(1.05);
    }
    button {
        width: 90%;
        padding: 10px;
        margin-top: 10px;
        background: linear-gradient(90deg, #00b09b, #96c93d);
        color: white;
        font-weight: bold;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: transform 0.3s ease;
    }
    button:hover { transform: scale(1.07); }
    .toggle {
        color: #fff;
        margin-top: 15px;
        font-size: 14px;
    }
    a {
        color: #00ffff;
        text-decoration: none;
        font-weight: bold;
        transition: color 0.3s;
    }
    a:hover { color: #fff; }
</style>

<script>
function showForm(form) {
    document.getElementById('loginForm').style.display = (form === 'login') ? 'block' : 'none';
    document.getElementById('registerForm').style.display = (form === 'register') ? 'block' : 'none';
}
</script>
</head>

<body>

<div class="container">
    <h2>🎓 User Portal</h2>
    <?php if ($message != ""): ?>
        <script>
            Swal.fire({
                text: "<?= $message ?>",
                icon: "info",
                confirmButtonColor: "#6a1b9a",
                background: "#f4f4f4",
                color: "#333"
            });
        </script>
    <?php endif; ?>

    <!-- LOGIN FORM -->
    <form id="loginForm" method="POST" style="display:block;">
        <input type="text" name="userid" placeholder="User ID" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
        <div class="toggle">Don’t have an account?
            <a href="#" onclick="showForm('register')">Register</a>
        </div>
    </form>

    <!-- REGISTER FORM -->
    <form id="registerForm" method="POST" style="display:none;">
        <input type="text" name="userid" placeholder="Create User ID" required>
        <input type="text" name="username" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email ID" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
            <option value="admin">Admin</option>
        </select>
        <button type="submit" name="register">Register</button>
        <div class="toggle">Already registered?
            <a href="#" onclick="showForm('login')">Login</a>
        </div>
    </form>
</div>
</body>
</html>
