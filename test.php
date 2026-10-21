<?php
// STEP 1 check page. Open http://localhost/portfolio/test.php  (delete this file before final submission)
require_once __DIR__ . '/lib/supabase.php';
$checks = [];
function check(string $name, callable $fn) {
    global $checks;
    try { $checks[] = [$name, true, (string)$fn()]; }
    catch (Throwable $e) {
        $m = $e->getMessage();
        if (stripos($m, 'SSL certificate') !== false) $m .= '  -> see README: "Fix the cURL SSL error on XAMPP"';
        $checks[] = [$name, false, $m];
    }
}
check('PHP version', fn() => PHP_VERSION);
check('cURL extension enabled', function () { if (!function_exists('curl_init')) throw new Exception('Open xampp/php/php.ini and remove the ; before extension=curl, then restart Apache'); return 'yes'; });
check('Supabase URL and key found', function () { if (!SUPABASE_URL || !SUPABASE_KEY) throw new Exception('Fill in config.local.php'); return SUPABASE_URL; });
check('Supabase Auth reachable', function () { $d = sb_http('GET', '/auth/v1/health'); return $d['name'] ?? 'ok'; });
check('Database tables exist', function () { $r = sb_http('GET', '/rest/v1/portfolios', ['query' => 'select=id&limit=1']); return 'portfolios table found (' . count($r) . ' public rows, normal because of Row Level Security)'; });
?>
<!doctype html><meta charset="utf-8"><title>Setup check</title>
<link rel="stylesheet" href="css/base.css">
<main class="wrap"><h1>Setup check</h1>
<?php foreach ($checks as [$n, $ok, $msg]): ?>
  <p><b><?= $ok ? '✅' : '❌' ?> <?= e($n) ?></b><br><span class="lead" style="font-size:1rem"><?= e($msg) ?></span></p>
<?php endforeach; ?>
</main>
