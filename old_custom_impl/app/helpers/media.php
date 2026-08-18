<?php
/**
 * Media and Image Asset Helper
 * Generates beautiful SVG placeholders dynamically if physical image files are not present.
 */

/**
 * Resolves a species image path, generating a high-quality inline SVG fallback if the image file is missing.
 * 
 * @param string|null $path File path relative to assets/images
 * @param string $name Common name of the species (for rendering text inside SVG)
 * @return string Image path or base64-encoded SVG data URI
 */
function getSpeciesImage(?string $path, string $name): string {
    $fullPath = __DIR__ . '/../../public/assets/images/' . $path;
    
    // Check if image exists physically
    if (!empty($path) && file_exists($fullPath)) {
        return '/assets/images/' . $path;
    }
    
    // Generate a beautiful, themed SVG fallback
    // We vary the background color slightly based on the name to make them distinct
    $hash = md5($name);
    $hue = (hexdec(substr($hash, 0, 2)) % 40) + 120; // 120-160 (forest green ranges)
    $bgGradientStart = "hsl({$hue}, 35%, 22%)";
    $bgGradientEnd = "hsl(" . ($hue + 15) . ", 25%, 15%)";
    
    $svg = '
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 250" width="100%" height="100%">
        <defs>
            <linearGradient id="grad-' . urlencode($name) . '" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:' . $bgGradientStart . ';stop-opacity:1" />
                <stop offset="100%" style="stop-color:' . $bgGradientEnd . ';stop-opacity:1" />
            </linearGradient>
        </defs>
        <rect width="100%" height="100%" fill="url(#grad-' . urlencode($name) . ')" />
        
        <!-- Subtle background pattern -->
        <g opacity="0.08">
            <circle cx="100" cy="125" r="80" fill="none" stroke="#fff" stroke-width="2" />
            <circle cx="300" cy="125" r="80" fill="none" stroke="#fff" stroke-width="2" />
            <line x1="0" y1="125" x2="400" y2="125" stroke="#fff" stroke-width="1" />
            <line x1="200" y1="0" x2="200" y2="250" stroke="#fff" stroke-width="1" />
        </g>
        
        <!-- Center Emblem -->
        <g transform="translate(200, 105)" stroke="#cfa844" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="0" cy="0" r="32" stroke="#cfa844" stroke-width="1" opacity="0.3" />
            <path d="M-12,-8 C-25,12 25,12 12,-8 C6,-15 -6,-15 -12,-8 Z" stroke-width="2.5" />
            <circle cx="0" cy="-2" r="5" fill="#cfa844" />
            <path d="M-8,-16 L-15,-25 L-2,-20 Z" fill="#cfa844" />
            <path d="M8,-16 L15,-25 L2,-20 Z" fill="#cfa844" />
        </g>
        
        <!-- Labels -->
        <text x="200" y="180" font-family="\'Playfair Display\', Georgia, serif" font-size="22" font-weight="bold" fill="#ffffff" text-anchor="middle">' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</text>
        <text x="200" y="205" font-family="\'Outfit\', sans-serif" font-size="11" font-weight="600" fill="#cfa844" letter-spacing="2" text-anchor="middle">SCOTTISH WILDLIFE RESOURCE</text>
    </svg>';
    
    return 'data:image/svg+xml;base64,' . base64_encode(trim($svg));
}
