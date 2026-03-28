<?php
session_start();
require_once "config.php";

/* 🔥 FORCE ERROR DISPLAY */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* 🔥 MYSQLI ERROR MODE */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$error = "";

/* ===============================
   CHECK DB CONNECTION
================================ */
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

/* ===============================
   AUTO REDIRECT IF ALREADY LOGGED IN
================================ */
if (isset($_SESSION['user_id'])) {

    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: dashboards/admin_dashboard.php");
            break;
        case 'accounts':
            header("Location: dashboards/accounts_dashboard.php");
            break;
        case 'library':
            header("Location: dashboards/library_dashboard.php");
            break;
        case 'lab':
            header("Location: dashboards/lab_dashboard.php");
            break;
        case 'exam':
            header("Location: dashboards/exam_dashboard.php");
            break;
        case 'student':
            header("Location: dashboards/student_dashboard.php");
            break;
        default:
            session_destroy();
            header("Location: login.php");
    }
    exit();
}

/* ===============================
   LOGIN LOGIC
================================ */
if (isset($_POST['login'])) {
    try {

        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if (empty($username) || empty($password)) {
            throw new Exception("All fields are required");
        }

        // Prepared Statement
        $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Plain password check
            if ($password === $user['password']) {

                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                switch ($user['role']) {

                    case 'admin':
                        header("Location: dashboards/admin_dashboard.php");
                        break;

                    case 'accounts':
                        header("Location: dashboards/accounts_dashboard.php");
                        break;

                    case 'library':
                        header("Location: dashboards/library_dashboard.php");
                        break;

                    case 'lab':
                        header("Location: dashboards/lab_dashboard.php");
                        break;

                    case 'exam':
                        header("Location: dashboards/exam_dashboard.php");
                        break;

                    case 'student':
                        header("Location: dashboards/student_dashboard.php");
                        break;

                    default:
                        throw new Exception("Invalid role assigned");
                }

                exit();
            } else {
                throw new Exception("Invalid username or password");
            }
        } else {
            throw new Exception("User not found");
        }

        $stmt->close();

    } catch (Exception $e) {
        $error = "❌ " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Student Dues Manager</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #d9eaff 0%, #b0d4f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 30%, rgba(30,136,229,0.08) 0%, transparent 70%);
        }

        .login-container {
            width: 100%;
            max-width: 440px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.96);
            border-radius: 32px;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
            padding: 2rem;
        }

        .logo-area {
            text-align: center;
            margin-bottom: 1.8rem;
        }

        .logo-area i {
            font-size: 3rem;
            color: #1e88e5;
        }

        .logo-area h2 {
            margin-top: 0.5rem;
            font-weight: 700;
        }

        .input-group {
            margin-bottom: 1.5rem;
        }

        .input-group input {
            width: 100%;
            padding: 0.8rem;
            border-radius: 30px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            padding: 0.8rem;
            border-radius: 30px;
            background: #1e88e5;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .error-message {
            background: #fee2e2;
            padding: 0.7rem;
            margin-bottom: 1rem;
            border-radius: 20px;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="login-card">

            <div class="logo-area">
                <i class="fas fa-user-shield"></i>
                <h2>Admin Access</h2>
                <p>Sign in to manage student dues</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="input-group">
                    <input type="text" name="username" placeholder="Enter username" required>
                </div>

                <div class="input-group">
                    <input type="password" name="password" placeholder="Enter password" required>
                </div>

                <button type="submit" name="login">Login</button>
            </form>

        </div>
    </div>

</body>

</html>
