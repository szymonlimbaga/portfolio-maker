<?php
// Settings come from environment variables (Render) or config.local.php (XAMPP).
$local = file_exists(__DIR__ . '/config.local.php') ? require __DIR__ . '/config.local.php' : [];

define('SUPABASE_URL', rtrim(getenv('SUPABASE_URL') ?: ($local['SUPABASE_URL'] ?? ''), '/'));
define('SUPABASE_KEY', getenv('SUPABASE_ANON_KEY') ?: ($local['SUPABASE_ANON_KEY'] ?? ''));

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function e($s): string { return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8'); }
