<?php
/**
 * Scottish Mammal Observations - Administrator Logout Action
 */

require_once __DIR__ . '/../app/helpers/auth.php';

logoutUser();

// Redirect back to homepage
header("Location: /index.php");
exit();
