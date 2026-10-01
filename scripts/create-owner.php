<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__).'/config/database.php';
$username = getenv('INITIAL_OWNER_USERNAME') ?: 'owner';
$name = getenv('INITIAL_OWNER_NAME') ?: 'Owner';
$password = getenv('INITIAL_OWNER_PASSWORD');
if (!$password || strlen($password) < 12) { fwrite(STDERR, "Set INITIAL_OWNER_PASSWORD (minimum 12 characters).\n"); exit(1); }
$pdo = db();
$check = $pdo->prepare('SELECT id FROM users WHERE username=?');
$check->execute([$username]);
if ($check->fetch()) { fwrite(STDERR, "Username already exists; no changes made.\n"); exit(1); }
$stmt = $pdo->prepare("INSERT INTO users (name,username,password,role,is_active) VALUES (?,?,?,'owner',1)");
$stmt->execute([$name,$username,password_hash($password,PASSWORD_DEFAULT)]);
echo "Owner created.\n";
