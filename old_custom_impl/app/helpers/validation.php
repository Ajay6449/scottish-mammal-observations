<?php
/**
 * Input Validation and Sanitization Helper
 */

/**
 * Escapes text output for insertion into HTML to prevent Cross-Site Scripting (XSS).
 * 
 * @param string|null $string String to escape
 * @return string Safe HTML escaped string
 */
function sanitizeOutput(?string $string): string {
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Basic sanitization for text inputs.
 * Removes leading/trailing spaces.
 * 
 * @param string $input Raw input string
 * @return string Sanitized string
 */
function cleanInput(string $input): string {
    return trim($input);
}

/**
 * Validates whether a given string is a valid email address.
 * 
 * @param string $email
 * @return bool
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validates latitude value (between -90 and 90).
 * 
 * @param mixed $lat
 * @return bool
 */
function isValidLatitude($lat): bool {
    if (!is_numeric($lat)) {
        return false;
    }
    $val = floatval($lat);
    return $val >= -90.0 && $val <= 90.0;
}

/**
 * Validates longitude value (between -180 and 180).
 * 
 * @param mixed $lng
 * @return bool
 */
function isValidLongitude($lng): bool {
    if (!is_numeric($lng)) {
        return false;
    }
    $val = floatval($lng);
    return $val >= -180.0 && $val <= 180.0;
}

/**
 * Validates if date is in YYYY-MM-DD format and is not in the future.
 * 
 * @param string $dateStr
 * @return bool
 */
function isValidObservationDate(string $dateStr): bool {
    $tempDate = explode('-', $dateStr);
    if (count($tempDate) !== 3) {
        return false;
    }
    
    $year = (int)$tempDate[0];
    $month = (int)$tempDate[1];
    $day = (int)$tempDate[2];
    
    if (!checkdate($month, $day, $year)) {
        return false;
    }
    
    // Ensure date is not in the future
    $obsTime = strtotime($dateStr);
    $currTime = time();
    return $obsTime <= $currTime;
}
