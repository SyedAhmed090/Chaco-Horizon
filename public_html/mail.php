<?php
/**
 * Enquiry endpoint for the quote overlay and the contact form.
 *
 * Receives JSON, applies spam checks, emails the desk and replies with JSON.
 * Runs on any cPanel PHP host.
 *
 * SETTINGS live in mail.config.php, ONE LEVEL ABOVE public_html (outside the
 * web root, so the SMTP password is never served or committed). Copy
 * mail.config.example.php from the repo root to that location and fill it in.
 * If the file is missing the defaults below apply, which use PHP mail() and
 * will not deliver until $to / $from are real mailboxes.
 *
 * Delivery: PHP mail() by default, which is reliable on cPanel when $from is a
 * mailbox on this domain (the server is SPF-authorised for it). For
 * authenticated SMTP, drop PHPMailer into php/PHPMailer/ (Exception.php,
 * PHPMailer.php, SMTP.php) and fill in the SMTP block in mail.config.php.
 */

ini_set('display_errors', '0');          // a PHP notice must never corrupt the JSON reply
error_reporting(E_ALL);

$to   = 'sales@example.com';
$from = 'website@example.com';
$subjectPrefix = '[Chaco Horizon]';
$smtp = ['host' => '', 'port' => 465, 'user' => '', 'pass' => '', 'secure' => 'ssl'];
$rateLimit = ['max' => 5, 'window' => 600];   // per IP: 5 submissions per 10 minutes

$configFile = dirname(__DIR__) . '/mail.config.php';
if (is_file($configFile)) {
  $cfg = include $configFile;
  if (is_array($cfg)) {
    $to   = $cfg['to']   ?? $to;
    $from = $cfg['from'] ?? $from;
    $subjectPrefix = $cfg['subjectPrefix'] ?? $subjectPrefix;
    $smtp = array_merge($smtp, $cfg['smtp'] ?? []);
    $rateLimit = array_merge($rateLimit, $cfg['rateLimit'] ?? []);
  }
}

// ---------------------------------------------------------------------------

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function reply(bool $ok, string $error = '', int $status = 200): void {
  http_response_code($status);
  echo json_encode($ok ? ['ok' => true] : ['ok' => false, 'error' => $error]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') reply(false, 'method', 405);

// --- same-origin check ------------------------------------------------------
// Browsers send Origin on cross-site POSTs; a curl loop from elsewhere is refused.
$host = strtolower($_SERVER['HTTP_HOST'] ?? '');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '') {
  $oHost = strtolower((string) parse_url($origin, PHP_URL_HOST));
  $strip = fn(string $h): string => preg_replace('/^www\./', '', preg_replace('/:\d+$/', '', $h));
  if ($strip($oHost) !== $strip($host)) reply(false, 'origin', 403);
}

// --- rate limit (per IP, file-based; no database needed) --------------------
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$rlFile = sys_get_temp_dir() . '/chaco-mail-' . sha1($ip . $host) . '.json';
$now = time();
$hits = [];
if (is_file($rlFile)) {
  $hits = json_decode((string) @file_get_contents($rlFile), true);
  if (!is_array($hits)) $hits = [];
}
$hits = array_values(array_filter($hits, fn($t) => is_int($t) && $now - $t < $rateLimit['window']));
if (count($hits) >= $rateLimit['max']) reply(false, 'rate', 429);

$raw = file_get_contents('php://input');
if (strlen($raw) > 65536) reply(false, 'too-large', 413);
$data = json_decode($raw, true);
if (!is_array($data)) reply(false, 'bad-json', 400);

$field = function (string $key, int $max = 2000) use ($data): string {
  $v = isset($data[$key]) && is_scalar($data[$key]) ? (string) $data[$key] : '';
  $v = trim(str_replace(["\r", "\0"], '', $v));
  return mb_substr($v, 0, $max);
};

// --- spam checks ------------------------------------------------------------
if ($field('website') !== '') reply(true);                       // honeypot: pretend success
// The page sends the ms timestamp at which the form was opened. Missing, zero or
// under three seconds old means a script, not a person: pretend success.
$opened = isset($data['t']) && is_numeric($data['t']) ? (int) $data['t'] : 0;
$elapsed = (int) round(microtime(true) * 1000) - $opened;
if ($opened <= 0 || $elapsed < 3000) reply(true);

$type  = $field('type') === 'contact' ? 'contact' : 'quote';
$name  = $field('name', 120);
$email = $field('email', 200);
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) reply(false, 'invalid', 422);
if (preg_match('/[\r\n]/', $email)) reply(false, 'invalid', 422);

// --- build the message ------------------------------------------------------
$lines = [];
if ($type === 'quote') {
  $subject = "$subjectPrefix Quote request — $name";
  $lines[] = "QUOTE REQUEST";
  $lines[] = "";
  $lines[] = "Category:     " . $field('cat');
  $lines[] = "Volume:       " . $field('vol');
  $lines[] = "Destination:  " . $field('port');
  $lines[] = "Incoterm:     " . $field('inco');
  $lines[] = "Timing:       " . $field('tim');
} else {
  $subject = "$subjectPrefix Message — $name";
  $lines[] = "CONTACT MESSAGE";
  $lines[] = "";
  $lines[] = $field('message', 5000);
}
$lines[] = "";
$lines[] = "Name:         $name";
$lines[] = "Company:      " . $field('company', 200);
$lines[] = "Email:        $email";
$lines[] = "Phone:        " . $field('phone', 60);
if ($type === 'quote' && $field('notes') !== '') {
  $lines[] = "";
  $lines[] = "Notes:";
  $lines[] = $field('notes', 5000);
}
$lines[] = "";
$lines[] = "—";
$lines[] = "Sent from " . $field('page', 200) . " at " . gmdate('Y-m-d H:i') . " UTC";
$lines[] = "IP " . $ip;
$body = implode("\n", $lines);

// --- send -------------------------------------------------------------------
// Count the attempt before sending, so a failing mailer cannot be hammered either.
$hits[] = $now;
@file_put_contents($rlFile, json_encode($hits), LOCK_EX);

$sent = false;
$why = '';

$phpmailer = __DIR__ . '/php/PHPMailer/PHPMailer.php';
if ($smtp['host'] !== '' && file_exists($phpmailer)) {
  require_once __DIR__ . '/php/PHPMailer/Exception.php';
  require_once $phpmailer;
  require_once __DIR__ . '/php/PHPMailer/SMTP.php';
  try {
    $m = new PHPMailer\PHPMailer\PHPMailer(true);
    $m->isSMTP();
    $m->Host = $smtp['host'];
    $m->Port = (int) $smtp['port'];
    $m->SMTPAuth = true;
    $m->Username = $smtp['user'];
    $m->Password = $smtp['pass'];
    $m->SMTPSecure = $smtp['secure'];
    $m->CharSet = 'UTF-8';
    $m->setFrom($from, 'Chaco Horizon website');
    $m->addAddress($to);
    $m->addReplyTo($email, $name);
    $m->Subject = $subject;
    $m->Body = $body;
    $sent = $m->send();
  } catch (Throwable $e) {
    $sent = false;
    $why = $e->getMessage();
  }
} else {
  // Display name in Reply-To: strip anything that could close the quoted
  // string or start a new mailbox, then MIME-encode it.
  $replyName = mb_encode_mimeheader(str_replace(['"', "'", '<', '>', ',', ';', "\\"], '', $name), 'UTF-8', 'B', "\r\n");
  $headers = [
    "From: Chaco Horizon website <$from>",
    "Reply-To: $replyName <$email>",
    "MIME-Version: 1.0",
    "Content-Type: text/plain; charset=UTF-8",
    "X-Mailer: PHP/" . phpversion(),
  ];
  $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
  $sent = @mail($to, $encodedSubject, $body, implode("\r\n", $headers), "-f$from");
  if (!$sent) $why = error_get_last()['message'] ?? 'mail() returned false';
}

if (!$sent) error_log("[chaco mail.php] send failed for $email: $why");

reply((bool) $sent, $sent ? '' : 'send-failed', $sent ? 200 : 500);
