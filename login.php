<?php
/**
 * Scottish Mammal Observations Database - Admin Login Page
 * Secure authentication with prepared statements, bcrypt verification, and CSRF protection
 *
 * SET08101 Web Technologies Coursework
 */

require_once 'includes/db.php';

// Generate CSRF token if not exists
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin/index.php');
    exit;
}

$pageTitle = 'Admin Login';
$currentPage = 'login';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Security token validation failed (CSRF mismatch).';
    } else {
        $usernameOrEmail = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($usernameOrEmail) || empty($password)) {
            $errors[] = 'Please enter both username/email and password.';
        } else {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare('SELECT id, username, email, password FROM users WHERE username = :username OR email = :email');
            $stmt->execute([':username' => $usernameOrEmail, ':email' => $usernameOrEmail]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID to prevent session fixation attacks
                session_regenerate_id(true);
                
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                
                header('Location: admin/index.php');
                exit;
            } else {
                $errors[] = 'Invalid username/email or password.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container" style="max-width: 450px; margin-top: var(--spacing-xxl); margin-bottom: var(--spacing-xxl);">
    <h2 style="text-align: center;">Administrator Portal</h2>
    <p style="text-align: center; color: var(--color-text-muted); margin-bottom: var(--spacing-lg);">Access platform management and sightings moderation</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: var(--spacing-md); font-size: 0.95rem;">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo e($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="chart-card">
        <form action="login.php" method="POST">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">

            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="admin" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: var(--spacing-sm);">Authenticate</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
