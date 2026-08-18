<?php
/**
 * Scottish Mammal Observations Database - Contact Page
 * Form with JavaScript client-side validation and secure PHP server-side validation
 *
 * SET08101 Web Technologies Coursework
 */

require_once 'includes/config.php';

$pageTitle = 'Contact Us';
$pageDescription = 'Get in touch with the Scottish Mammal Observations team. Submit feedback, report issues, or ask questions about biodiversity observations.';
$currentPage = 'contact';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$successMessage = '';

$name = '';
$email = '';
$subject = '';
$message = '';

// Process Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Security validation failed (CSRF token mismatch).';
    } else {
        // Retrieve and sanitize inputs
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Validation constraints
        if (empty($name)) {
            $errors[] = 'Name is required.';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        if (empty($subject)) {
            $errors[] = 'Subject is required.';
        }
        
        if (empty($message)) {
            $errors[] = 'Message is required.';
        } elseif (strlen($message) < 10) {
            $errors[] = 'Message must be at least 10 characters long.';
        }

        // If no errors, process the contact message (e.g., save to log, send email, etc.)
        if (empty($errors)) {
            $successMessage = "Thank you, " . e($name) . "! Your message has been processed successfully.";
            // Clear form inputs after success
            $name = $email = $subject = $message = '';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container" style="max-width: 600px; margin-top: var(--spacing-xl); margin-bottom: var(--spacing-xl);">
    <h2>Contact Wildlife Scotland</h2>
    <p style="margin-bottom: var(--spacing-lg);">Have any questions about species occurrences, dataset licensing, or want to report an observation discrepancy? Drop us a message.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: var(--spacing-md);">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo e($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($successMessage): ?>
        <div class="alert alert-success">
            <?php echo e($successMessage); ?>
        </div>
    <?php endif; ?>

    <div class="chart-card">
        <form id="contactForm" action="contact.php" method="POST" novalidate>
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo e($name); ?>" required>
                <span class="error-feedback" style="color: var(--color-error); font-size: 0.85rem; display: none;">Please enter your name.</span>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo e($email); ?>" required>
                <span class="error-feedback" style="color: var(--color-error); font-size: 0.85rem; display: none;">Please enter a valid email address.</span>
            </div>

            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" class="form-control" value="<?php echo e($subject); ?>" required>
                <span class="error-feedback" style="color: var(--color-error); font-size: 0.85rem; display: none;">Please enter a subject.</span>
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="5" class="form-control" required><?php echo e($message); ?></textarea>
                <span class="error-feedback" style="color: var(--color-error); font-size: 0.85rem; display: none;">Message must be at least 10 characters long.</span>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Message</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
