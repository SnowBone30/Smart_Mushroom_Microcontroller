<?php
// Start session for authentication
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Handle login form submission
$error = '';
$redirect = trim($_GET['redirect'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = trim($_POST['redirect'] ?? $_GET['redirect'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        require_once 'db.php';

        $user = authenticate_user($username, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($redirect) {
                $redirect_path = parse_url($redirect, PHP_URL_PATH) ?: '';
                if ($redirect_path !== '' && stripos($redirect_path, 'login.php') === false && strpos($redirect_path, '://') === false) {
                    if ($redirect_path[0] !== '/') {
                        $redirect_path = '/' . $redirect_path;
                    }
                    header('Location: ' . $redirect_path);
                    exit;
                }
            }

            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Mushroom Farm</title>

<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Share+Tech+Mono&family=Exo+2:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg);
    padding: 20px;
}

.login-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 40px;
    width: 100%;
    max-width: 400px;
    text-align: center;
}

.login-logo {
    margin-bottom: 30px;
}

.login-icon {
    font-size: 48px;
    margin-bottom: 10px;
}

.login-title {
    font-family: var(--head);
    font-weight: 700;
    font-size: 24px;
    letter-spacing: 2px;
    color: var(--bright);
    margin-bottom: 8px;
}

.login-subtitle {
    font-family: var(--mono);
    font-size: 12px;
    color: var(--dim);
    letter-spacing: 1px;
}

.login-form {
    margin-top: 30px;
}

.login-error {
    background: rgba(255, 77, 77, 0.1);
    border: 1px solid var(--red);
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 20px;
    font-family: var(--mono);
    font-size: 12px;
    color: var(--red);
}

.login-input {
    width: 100%;
    background: var(--bg-card2);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 16px;
    color: var(--bright);
    font-family: var(--mono);
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
}

.login-input:focus {
    border-color: var(--blue);
}

.login-btn {
    width: 100%;
    background: var(--blue);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 14px 16px;
    font-family: var(--head);
    font-weight: 700;
    font-size: 14px;
    letter-spacing: 1px;
    cursor: pointer;
    transition: background 0.2s;
}

.login-btn:hover {
    background: #5aadff;
}

.login-footer {
    margin-top: 20px;
    font-family: var(--mono);
    font-size: 11px;
    color: var(--dim);
}

.login-footer strong {
    color: var(--bright);
}
</style>
</head>

<body>
<div class="login-container">
    <div class="login-card">
        <div class="login-logo">
            <div class="login-icon">🍄</div>
            <div class="login-title">MUSHROOM FARM</div>
            <div class="login-subtitle">SMART MICROCLIMATE CONTROLLER</div>
        </div>

        <?php if ($error): ?>
        <div class="login-error">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="post" class="login-form">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
            <input type="text" name="username" class="login-input" placeholder="USERNAME" required autofocus>

            <input type="password" name="password" class="login-input" placeholder="PASSWORD" required>

            <button type="submit" class="login-btn">LOGIN →</button>
        </form>

        <div class="login-footer">
            Default credentials:<br>
            <strong>Username:</strong> admin<br>
            <strong>Password:</strong> admin123
        </div>
    </div>
</div>
</body>
</html>