<?php
require_once __DIR__ . '/../lib/supabase.php';

/* ---------- CSRF protection for forms that change data ---------- */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_ok(): bool {
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']);
}

/* ---------- shared page top: <head>, header bar, menu, opening <main> ---------- */
function page_head(string $title, string $mainClass = 'wrap', array $extraCss = []): void {
    $cur  = basename($_SERVER['SCRIPT_NAME']);
    $user = auth_user();
    $a = fn(string $href, string $text) =>
        '<a href="' . $href . '"' . ($cur === $href ? ' aria-current="page"' : '') . '>' . $text . '</a>';
    $nav = $a('index.php', 'Home') . $a('templates.php', 'Templates') . $a('features.php', 'Features')
         . ($user
              ? $a('manage.php', 'My portfolios')
                . $a('form.php', 'Create portfolio')
                . $a('logout.php', 'Log out')
              : $a('login.php', 'Log in'));
    ?>
<!doctype html>
<html lang="en">
<head>
  <title><?= e($title) ?> | Portfolio Maker</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/base.css">
<?php foreach ($extraCss as $c): ?>
  <link rel="stylesheet" href="<?= e($c) ?>">
<?php endforeach; ?>
</head>
<body>
  <header class="bar"><a class="brand" href="index.php">Portfolio Maker</a>
    <nav id="nav"><?= $nav ?></nav>
  </header>
  <main class="<?= e($mainClass) ?>">
<?php
}

/* ---------- shared page bottom ---------- */
function page_foot(string $scripts = ''): void {
    echo "  </main>\n";
    echo $scripts . "\n";
    echo "</body>\n</html>\n";
}
