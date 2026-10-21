<?php
require_once __DIR__ . '/../config.php';

/* ---------- low-level HTTP call to Supabase ---------- */
function sb_http(string $method, string $path, array $o = []): array {
    $url = SUPABASE_URL . $path . (!empty($o['query']) ? '?' . $o['query'] : '');
    $headers = ['apikey: ' . SUPABASE_KEY, 'Authorization: Bearer ' . ($o['token'] ?? SUPABASE_KEY)];
    $body = null;
    if (isset($o['json'])) { $headers[] = 'Content-Type: application/json'; $body = json_encode($o['json']); }
    elseif (isset($o['raw'])) { $headers[] = 'Content-Type: ' . $o['mime']; $body = $o['raw']; }
    foreach ($o['headers'] ?? [] as $h) $headers[] = $h;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 30,
    ]);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    $res = curl_exec($ch);
    if ($res === false) { $err = curl_error($ch); curl_close($ch); throw new Exception('Network error: ' . $err); }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($res, true);
    if ($code >= 400) {
        $msg = is_array($data) ? ($data['msg'] ?? $data['message'] ?? $data['error_description'] ?? $data['error'] ?? $res) : $res;
        throw new Exception($msg, $code);
    }
    return is_array($data) ? $data : [];
}

/* ---------- login / session ---------- */
function auth_save(array $d): void {
    $_SESSION['sb'] = [
        'access_token'  => $d['access_token'],
        'refresh_token' => $d['refresh_token'] ?? '',
        'expires_at'    => time() + (int)($d['expires_in'] ?? 3600),
        'user_id'       => $d['user']['id'] ?? '',
        'email'         => $d['user']['email'] ?? '',
    ];
}
function auth_signup(string $email, string $pw): array {
    $d = sb_http('POST', '/auth/v1/signup', ['json' => ['email' => $email, 'password' => $pw]]);
    if (!empty($d['access_token'])) { auth_save($d); return ['logged_in' => true]; }
    return ['logged_in' => false];   // email confirmation is switched on in Supabase
}
function auth_login(string $email, string $pw): void {
    $d = sb_http('POST', '/auth/v1/token', ['query' => 'grant_type=password', 'json' => ['email' => $email, 'password' => $pw]]);
    auth_save($d);
}
function auth_logout(): void { unset($_SESSION['sb']); }

/** Returns a valid access token, refreshing it when it is about to expire. Null if not logged in. */
function auth_token(): ?string {
    $s = $_SESSION['sb'] ?? null;
    if (!$s) return null;
    if ($s['expires_at'] - 60 < time()) {
        try {
            $d = sb_http('POST', '/auth/v1/token', ['query' => 'grant_type=refresh_token', 'json' => ['refresh_token' => $s['refresh_token']]]);
            auth_save($d);
        } catch (Exception $e) { auth_logout(); return null; }
    }
    return $_SESSION['sb']['access_token'];
}
function auth_user(): ?array { return auth_token() ? $_SESSION['sb'] : null; }

function require_auth(): array {
    $u = auth_user();
    if (!$u) {
        $self = basename($_SERVER['SCRIPT_NAME']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
        header('Location: login.php?next=' . urlencode($self));
        exit;
    }
    return $u;
}

/* ---------- database (Supabase REST). Uses the user's token so Row Level Security applies ---------- */
function db_select(string $table, string $query = ''): array {
    return sb_http('GET', "/rest/v1/$table", ['query' => $query, 'token' => auth_token()]);
}
function db_insert(string $table, array $rows): array {
    return sb_http('POST', "/rest/v1/$table", ['json' => $rows, 'token' => auth_token(), 'headers' => ['Prefer: return=representation']]);
}
function db_update(string $table, string $filter, array $data): array {
    return sb_http('PATCH', "/rest/v1/$table", ['query' => $filter, 'json' => $data, 'token' => auth_token(), 'headers' => ['Prefer: return=representation']]);
}
function db_delete(string $table, string $filter): void {
    sb_http('DELETE', "/rest/v1/$table", ['query' => $filter, 'token' => auth_token()]);
}

/* ---------- profile pictures (Supabase Storage, bucket "avatars") ---------- */
function storage_upload(string $bucket, string $path, string $tmpFile, string $mime): string {
    $safe = implode('/', array_map('rawurlencode', explode('/', $path)));
    sb_http('POST', "/storage/v1/object/$bucket/$safe", ['raw' => file_get_contents($tmpFile), 'mime' => $mime, 'token' => auth_token()]);
    return SUPABASE_URL . "/storage/v1/object/public/$bucket/$safe";
}
