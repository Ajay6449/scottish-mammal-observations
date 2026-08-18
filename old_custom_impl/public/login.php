<?php
/**
 * Scottish Mammal Observations - Administrator Login Page
 */

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/validation.php';
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/helpers/csrf.php';

$db = getDBConnection();

// Redirect to admin portal if already logged in as admin
if (isAdmin()) {
    header("Location: /admin/index.php");
    exit();
}

$error = '';
$usernameOrEmail = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. CSRF Verification
    $csrfToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!validateCSRFToken($csrfToken)) {
        http_response_code(403);
        die("CSRF Token validation failed. Request blocked.");
    }
    
    // 2. Fetch input data
    $usernameOrEmail = isset($_POST['username_or_email']) ? cleanInput($_POST['username_or_email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // 3. Validation
    if (empty($usernameOrEmail) || empty($password)) {
        $error = "Please enter both username/email and password.";
    } else {
        try {
            // Find user in DB
            $stmt = $db->prepare("SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1");
            $stmt->execute([
                'username' => $usernameOrEmail,
                'email' => $usernameOrEmail
            ]);
            $user = $stmt->fetch();
            
            // Verify password using password_verify
            if ($user && password_verify($password, $user['password_hash'])) {
                // Login user and regenerate session
                loginUser($user);
                
                // Redirect back to request URL or default to admin dashboard
                secureSessionStart();
                $redirectUrl = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : '/admin/index.php';
                unset($_SESSION['redirect_url']);
                
                header("Location: " . $redirectUrl);
                exit();
            } else {
                $error = "Invalid username/email or password.";
            }
        } catch (\PDOException $e) {
            $error = "System error during authentication. Please try again later.";
        }
    }
}

$pageTitle = "Administrator Login | Scottish Mammal Observations";
$pageDescription = "Secure login portal for Scottish Mammal Observations administrators.";

require_once __DIR__ . '/../views/layouts/header.php';
?>

<div class="container" style="max-width: 450px; padding: var(--spacing-xl) var(--spacing-lg);">
    <div style="text-align: center; margin-bottom: var(--spacing-xl);">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-primary); margin-bottom: var(--spacing-sm);">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
        <h1 style="font-size: 2rem; margin-bottom: var(--spacing-xs);">Administrator Login</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">Access the management dashboard to moderate sightings and manage species profile logs.</p>
    </div>

    <!-- Error state display -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span><?php echo $error; ?></span>
        </div>
    <?php endif; ?>

    <form action="/login.php" method="POST" class="card" style="padding: var(--spacing-xl); box-shadow: var(--shadow-md);">
        <?php echo csrfField(); ?>
        
        <div class="form-group">
            <label for="username_or_email">Username or Email Address</label>
            <input type="text" name="username_or_email" id="username_or_email" class="form-control" placeholder="e.g. admin" value="<?php echo sanitizeOutput($usernameOrEmail); ?>" required autofocus>
        </div>
        
        <div class="form-group" style="margin-bottom: var(--spacing-xl);">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.05rem; padding: 0.8rem;">Access Admin Portal</button>
    </form>
    
    <div style="text-align: center; margin-top: var(--spacing-lg);">
        <p style="font-size: 0.9rem; color: var(--color-text-muted);">
            Demo Admin Credentials:<br>
            <code style="background: #f0ede6; padding: 2px 6px; border-radius: 3px; font-size: 0.85rem; display: inline-block; margin-top: 4px;">admin</code> / <code style="background: #f0ede6; padding: 2px 6px; border-radius: 3px; font-size: 0.85rem; display: inline-block;">Highlands2026!</code>
        </p>
        <p style="font-size: 0.95rem; margin-top: var(--spacing-md);"><a href="/index.php">&larr; Return to Home Page</a></p>
    </div>
</div>

<?php
require_once __DIR__ . '/../views/layouts/footer.php';
?>
