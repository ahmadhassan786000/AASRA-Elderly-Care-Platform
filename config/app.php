<?php
// Application settings. Set APP_DEBUG to true only on a development machine.
define('APP_DEBUG', false);
define('APP_NAME', 'AASRA');

// Base URL path is detected automatically (works at http://localhost/aasra and at a domain root).
$__script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
$__base = rtrim(dirname($__script), '/');
define('BASE_URL', ($__base === '.' || $__base === '\\') ? '' : $__base);

// Social links shown in the public footer. Replace with your real pages.
define('SOCIAL_LINKS', [
    ['Facebook',  'bi-facebook',  'https://www.facebook.com/'],
    ['Instagram', 'bi-instagram', 'https://www.instagram.com/'],
    ['LinkedIn',  'bi-linkedin',  'https://www.linkedin.com/'],
    ['YouTube',   'bi-youtube',   'https://www.youtube.com/'],
]);
