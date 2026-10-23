<?php
$DB_HOST = getenv('PINKCMS_DB_HOST') ?: '127.0.0.1';
$DB_USER = getenv('PINKCMS_DB_USER') ?: '';
$DB_PASS = getenv('PINKCMS_DB_PASS');
$DB_PASS = $DB_PASS === false ? '' : $DB_PASS;
$DB_NAME = getenv('PINKCMS_DB_NAME') ?: 'pinkcms';
$DB_PORT = (int)(getenv('PINKCMS_DB_PORT') ?: 3306);

if ($DB_USER === '') {
    error_log('PinkCMS database configuration is incomplete: set PINKCMS_DB_USER.');
    http_response_code(500);
    die('Set the PinkCMS database environment variables before starting the site.');
}

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if (!$conn) {
    error_log('PinkCMS database connection failed: '.mysqli_connect_error());
    http_response_code(500);
    die('Unable to connect to the database. Check the server configuration.');
}
mysqli_set_charset($conn, 'utf8mb4');

$SETTINGS = [];
$res = mysqli_query($conn, 'SELECT skey, svalue FROM settings');
if (!$res) {
    error_log('PinkCMS settings query failed: '.mysqli_error($conn));
    http_response_code(500);
    die('Unable to load site settings.');
}
while ($r = mysqli_fetch_assoc($res)) $SETTINGS[$r['skey']] = $r['svalue'];

function setting($key, $default='') {
    global $SETTINGS;
    return isset($SETTINGS[$key]) ? $SETTINGS[$key] : $default;
}
function saveSetting($key, $value) {
    global $conn, $SETTINGS;
    $k = mysqli_real_escape_string($conn, $key);
    $v = mysqli_real_escape_string($conn, $value);
    if (!mysqli_query($conn, "INSERT INTO settings (skey,svalue) VALUES ('$k','$v') ON DUPLICATE KEY UPDATE svalue='$v'")) {
        error_log('PinkCMS setting save failed: '.mysqli_error($conn));
        return false;
    }
    $SETTINGS[$key] = $value;
    return true;
}
function logActivity($action) {
    global $conn;
    $a = mysqli_real_escape_string($conn, $action);
    if (!mysqli_query($conn, "INSERT INTO activity (action,time) VALUES ('$a',NOW())")) {
        error_log('PinkCMS activity log failed: '.mysqli_error($conn));
    }
}
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS'])]);
    session_start();
}
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
function isAdmin() {
    return !empty($_SESSION['pinkcms_admin']);
}
